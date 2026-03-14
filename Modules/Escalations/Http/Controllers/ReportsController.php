<?php

namespace Modules\Escalations\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Escalations\Entities\Escalation;
use Modules\Escalations\Entities\SubCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Modules\Escalations\Exports\EscalationReportExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportsController extends Controller
{
    /**
     * Display the reports index page.
     */
    public function index()
    {
        return view('escalations::reports.index');
    }

    /**
     * Display SLA Report.
     */
    public function slaReport(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        // Overall SLA metrics
        $totalEscalations = Escalation::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        
        $closedEscalations = Escalation::whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereIn('status', ['Scheduled-Closed', 'Escalated-Closed'])
            ->count();

        // Assuming 4 hours SLA for escalations
        $withinSla = Escalation::whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereIn('status', ['Scheduled-Closed', 'Escalated-Closed'])
            ->whereRaw('TIMESTAMPDIFF(HOUR, created_at, updated_at) <= 4')
            ->count();

        $outsideSla = Escalation::whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereIn('status', ['Scheduled-Closed', 'Escalated-Closed'])
            ->whereRaw('TIMESTAMPDIFF(HOUR, created_at, updated_at) > 4')
            ->count();

        $slaCompliancePercentage = $closedEscalations > 0 
            ? round(($withinSla / $closedEscalations) * 100, 2) 
            : 0;

        // Daily SLA breakdown
        $dailyMetrics = DB::table('escalations')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status IN ("Scheduled-Closed", "Escalated-Closed") THEN 1 ELSE 0 END) as closed'),
                DB::raw('SUM(CASE WHEN status IN ("Scheduled-Closed", "Escalated-Closed") AND TIMESTAMPDIFF(HOUR, created_at, updated_at) <= 4 THEN 1 ELSE 0 END) as within_sla'),
                DB::raw('SUM(CASE WHEN status IN ("Scheduled-Closed", "Escalated-Closed") AND TIMESTAMPDIFF(HOUR, created_at, updated_at) > 4 THEN 1 ELSE 0 END) as outside_sla')
            )
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        return view('escalations::reports.sla', compact(
            'totalEscalations',
            'closedEscalations',
            'withinSla',
            'outsideSla',
            'slaCompliancePercentage',
            'dailyMetrics',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Display Productivity Report by User.
     */
    public function productivityReport(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        // Get user productivity metrics
        $userMetrics = DB::table('escalations')
            ->join('users', 'escalations.assigned_to', '=', 'users.id')
            ->select(
                'users.id',
                'users.name',
                DB::raw('COUNT(*) as total_assigned'),
                DB::raw('SUM(CASE WHEN escalations.status IN ("Scheduled-Closed", "Escalated-Closed") THEN 1 ELSE 0 END) as total_closed'),
                DB::raw('SUM(CASE WHEN escalations.status IN ("Scheduled-Closed", "Escalated-Closed") AND TIMESTAMPDIFF(HOUR, escalations.created_at, escalations.updated_at) <= 4 THEN 1 ELSE 0 END) as closed_within_sla'),
                DB::raw('SUM(CASE WHEN escalations.status IN ("Scheduled-Closed", "Escalated-Closed") AND TIMESTAMPDIFF(HOUR, escalations.created_at, escalations.updated_at) > 4 THEN 1 ELSE 0 END) as closed_outside_sla'),
                DB::raw('ROUND(AVG(CASE WHEN escalations.status IN ("Scheduled-Closed", "Escalated-Closed") THEN TIMESTAMPDIFF(HOUR, escalations.created_at, escalations.updated_at) END), 2) as avg_resolution_time')
            )
            ->whereBetween('escalations.created_at', [$dateFrom, $dateTo])
            ->whereNotNull('escalations.assigned_to')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_closed')
            ->get();

        // Calculate SLA compliance percentage for each user
        foreach ($userMetrics as $metric) {
            $metric->sla_compliance_percentage = $metric->total_closed > 0 
                ? round(($metric->closed_within_sla / $metric->total_closed) * 100, 2) 
                : 0;
        }

        return view('escalations::reports.productivity', compact(
            'userMetrics',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Display Sub Category Report.
     */
    public function subCategoryReport(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        // Get escalated items by sub category
        $subCategoryMetrics = DB::table('escalations')
            ->join('subcategories', 'escalations.sub_category_id', '=', 'subcategories.id')
            ->join('categories', 'subcategories.category_id', '=', 'categories.id')
            ->select(
                'categories.category_name',
                'subcategories.sub_category_name',
                'subcategories.id as sub_category_id',
                DB::raw('COUNT(*) as total_escalations'),
                DB::raw('SUM(CASE WHEN escalations.status IN ("Scheduled-Closed", "Escalated-Closed") THEN 1 ELSE 0 END) as closed_escalations'),
                DB::raw('SUM(CASE WHEN escalations.status NOT IN ("Scheduled-Closed", "Escalated-Closed") THEN 1 ELSE 0 END) as open_escalations'),
                DB::raw('SUM(CASE WHEN escalations.status IN ("Scheduled-Closed", "Escalated-Closed") AND TIMESTAMPDIFF(HOUR, escalations.created_at, escalations.updated_at) <= 4 THEN 1 ELSE 0 END) as closed_within_sla'),
                DB::raw('ROUND(AVG(CASE WHEN escalations.status IN ("Scheduled-Closed", "Escalated-Closed") THEN TIMESTAMPDIFF(HOUR, escalations.created_at, escalations.updated_at) END), 2) as avg_resolution_time')
            )
            ->whereBetween('escalations.created_at', [$dateFrom, $dateTo])
            ->groupBy('categories.category_name', 'subcategories.sub_category_name', 'subcategories.id')
            ->orderByDesc('total_escalations')
            ->get();

        // Calculate SLA compliance percentage for each sub category
        foreach ($subCategoryMetrics as $metric) {
            $metric->sla_compliance_percentage = $metric->closed_escalations > 0 
                ? round(($metric->closed_within_sla / $metric->closed_escalations) * 100, 2) 
                : 0;
        }

        // Group by category for better display
        $groupedMetrics = $subCategoryMetrics->groupBy('category_name');

        return view('escalations::reports.sub_category', compact(
            'subCategoryMetrics',
            'groupedMetrics',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Export escalation reports to Excel
     */
    public function exportExcel(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:sla,productivity,sub_category,escalations',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from'
        ]);

        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $reportType = $request->report_type;

        $fileName = 'escalation_' . $reportType . '_report_' . $dateFrom . '_to_' . $dateTo . '.xlsx';

        return Excel::download(
            new EscalationReportExport($dateFrom, $dateTo, $reportType),
            $fileName
        );
    }
}
