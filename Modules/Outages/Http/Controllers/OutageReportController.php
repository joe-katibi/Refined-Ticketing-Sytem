<?php

namespace Modules\Outages\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Modules\Outages\Models\Outage;
use Modules\Outages\Models\Olt;
use Modules\Outages\Services\OutageNotificationService;
use App\Models\Team;
use App\Models\TeamType;
use App\Models\User;

class OutageReportController extends OutagesController
{
    protected $notificationService;

    public function __construct(OutageNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Display the reports index page.
     */
    public function index()
    {
        // Get date range from request or default to last month
        $startDate = request('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = request('end_date', now()->format('Y-m-d'));
        $teamFilter = request('team_filter');

        // Get team types for filter dropdown (Infrastructure teams)
        $teams = \App\Models\TeamType::whereHas('department', function($q) {
            $q->where('department_name', 'Infrastructure');
        })->orderBy('type_name')->get();

        // Build base query with date filters (unified outages table)
        $outagesQuery = Outage::whereBetween('start_time', [$startDate, $endDate]);

        // Apply team filter if selected
        if ($teamFilter) {
            $outagesQuery->where('assigned_team_id', $teamFilter);
        }

        // Calculate SLA metrics
        $totalOutages = $outagesQuery->count();
        $breachedOutages = $outagesQuery->where('sla_breached', true)->count();
        $complianceRate = $totalOutages > 0 ? round((($totalOutages - $breachedOutages) / $totalOutages) * 100, 1) : 100;
        
        $slaMetrics = [
            'compliance_rate' => $complianceRate,
            'breach_count' => $breachedOutages,
            'total_outages' => $totalOutages
        ];

        // Calculate productivity metrics
        $resolvedOutages = $outagesQuery->where('status', 'Resolved')->count();
        $avgResolutionTime = $outagesQuery->where('status', 'Resolved')
            ->whereNotNull('end_time')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, start_time, end_time)) as avg_hours')
            ->value('avg_hours');
        
        $productivityMetrics = [
            'total_resolved' => $resolvedOutages,
            'avg_resolution_time' => $avgResolutionTime ? round($avgResolutionTime, 1) . 'h' : 'N/A'
        ];

        // Calculate SLA breakdown
        $withinSla = $totalOutages - $breachedOutages;
        $slaBreakdown = [
            'within_sla' => $withinSla,
            'breached_sla' => $breachedOutages
        ];

        // Calculate trend metrics
        $monthlyAvg = round($totalOutages / max(1, now()->parse($startDate)->diffInMonths(now()->parse($endDate)) ?: 1), 1);
        $previousPeriodStart = now()->parse($startDate)->subMonth()->format('Y-m-d');
        $previousPeriodEnd = now()->parse($endDate)->subMonth()->format('Y-m-d');
        $previousPeriodCount = Outage::whereBetween('start_time', [$previousPeriodStart, $previousPeriodEnd])->count();
        
        $trendDirection = $totalOutages > $previousPeriodCount ? '↑' : ($totalOutages < $previousPeriodCount ? '↓' : '→');
        
        $trendMetrics = [
            'monthly_avg' => $monthlyAvg,
            'trend_direction' => $trendDirection
        ];

        // Calculate impact metrics (now using unified outages table with impact/urgency fields)
        $highImpactCount = $outagesQuery->where(function($q) {
            $q->where('impact', 'Critical')->orWhere('impact', 'High');
        })->count();
        $totalDowntime = $outagesQuery->where('status', 'Resolved')
            ->whereNotNull('end_time')
            ->selectRaw('SUM(TIMESTAMPDIFF(HOUR, start_time, end_time)) as total_hours')
            ->value('total_hours');
        
        // Calculate total customers affected
        $totalCustomersAffected = $outagesQuery->whereNotNull('total_customers_affected')
            ->sum('total_customers_affected');
        
        $impactMetrics = [
            'high_impact_count' => $highImpactCount,
            'total_downtime' => $totalDowntime ? round($totalDowntime, 1) . 'h' : '0h',
            'total_customers_affected' => $totalCustomersAffected
        ];

        // Calculate root cause metrics
        $topCause = $outagesQuery->select('root_cause')
            ->whereNotNull('root_cause')
            ->groupBy('root_cause')
            ->orderByRaw('COUNT(*) DESC')
            ->value('root_cause') ?: 'Unknown';
        
        $categoriesCount = $outagesQuery->whereNotNull('root_cause')
            ->distinct('root_cause')
            ->count('root_cause');
        
        $rootCauseMetrics = [
            'top_cause' => $topCause,
            'categories_count' => $categoriesCount
        ];

        return view($this->view('reports.index'), compact(
            'teams',
            'slaMetrics',
            'productivityMetrics', 
            'slaBreakdown',
            'trendMetrics',
            'impactMetrics',
            'rootCauseMetrics'
        ));
    }

    /**
     * Generate SLA compliance report.
     */
    public function sla(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        // Overall SLA metrics
        $totalOutages = Outage::whereBetween('created_at', [$startDate, $endDate])->count();
        $slaBreachedOutages = Outage::where('sla_breached', true)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        $slaComplianceRate = $totalOutages > 0 ? 
            round((($totalOutages - $slaBreachedOutages) / $totalOutages) * 100, 2) : 100;

        // SLA metrics by priority
        $slaByPriority = Outage::select('priority')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COUNT(CASE WHEN sla_breached = 1 THEN 1 END) as breached')
            ->selectRaw('ROUND((COUNT(*) - COUNT(CASE WHEN sla_breached = 1 THEN 1 END)) / COUNT(*) * 100, 2) as compliance_rate')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('priority')
            ->get();

        // SLA metrics by team
        $slaByTeam = Outage::select('team_types.type_name as team_name')
            ->selectRaw('COUNT(outages.id) as total')
            ->selectRaw('COUNT(CASE WHEN outages.sla_breached = 1 THEN 1 END) as breached')
            ->selectRaw('ROUND((COUNT(outages.id) - COUNT(CASE WHEN outages.sla_breached = 1 THEN 1 END)) / COUNT(outages.id) * 100, 2) as compliance_rate')
            ->leftJoin('team_types', 'outages.assigned_team_id', '=', 'team_types.id')
            ->leftJoin('departments', 'team_types.department_id', '=', 'departments.id')
            ->where('departments.department_name', 'Infrastructure')
            ->whereBetween('outages.created_at', [$startDate, $endDate])
            ->groupBy('team_types.id', 'team_types.type_name')
            ->having('total', '>', 0)
            ->orderBy('compliance_rate', 'desc')
            ->get();

        // Detailed SLA breached outages
        $breachedOutages = Outage::with(['assignedTeam', 'assignee'])
            ->where('sla_breached', true)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('sla_breach_time', 'desc')
            ->get();

        return view($this->view('reports.sla'), compact(
            'totalOutages', 'slaBreachedOutages', 'slaComplianceRate',
            'slaByPriority', 'slaByTeam', 'breachedOutages', 'startDate', 'endDate'
        ));
    }

    /**
     * Generate productivity report.
     */
    public function productivity(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        // Team productivity
        $teamProductivity = \App\Models\TeamType::select('team_types.type_name as name')
            ->selectRaw('COUNT(outages.id) as total_outages')
            ->selectRaw('COUNT(CASE WHEN outages.status = "Resolved" THEN 1 END) as resolved_outages')
            ->selectRaw('COUNT(CASE WHEN outages.status = "Closed" THEN 1 END) as closed_outages')
            ->selectRaw('AVG(CASE WHEN outages.status = "Resolved" AND outages.end_time IS NOT NULL 
                THEN TIMESTAMPDIFF(MINUTE, outages.start_time, outages.end_time) END) as avg_resolution_time')
            ->leftJoin('outages', 'team_types.id', '=', 'outages.assigned_team_id')
            ->leftJoin('departments', 'team_types.department_id', '=', 'departments.id')
            ->where('departments.department_name', 'Infrastructure')
            ->where('team_types.status', 'Active')
            ->whereBetween('outages.created_at', [$startDate, $endDate])
            ->groupBy('team_types.id', 'team_types.type_name')
            ->having('total_outages', '>', 0)
            ->orderBy('resolved_outages', 'desc')
            ->get();

        // Individual user productivity
        $userProductivity = User::select('users.name')
            ->selectRaw('COUNT(outages.id) as total_assigned')
            ->selectRaw('COUNT(CASE WHEN outages.status = "Resolved" THEN 1 END) as resolved_outages')
            ->selectRaw('COUNT(resolved_outages.id) as total_resolved')
            ->selectRaw('AVG(CASE WHEN resolved_outages.end_time IS NOT NULL 
                THEN TIMESTAMPDIFF(MINUTE, resolved_outages.start_time, resolved_outages.end_time) END) as avg_resolution_time')
            ->leftJoin('outages', 'users.id', '=', 'outages.assigned_to')
            ->leftJoin('outages as resolved_outages', 'users.id', '=', 'resolved_outages.resolved_by')
            ->where('users.user_status', 'Active')
            ->whereBetween('outages.created_at', [$startDate, $endDate])
            ->orWhereBetween('resolved_outages.created_at', [$startDate, $endDate])
            ->groupBy('users.id', 'users.name')
            ->having('total_assigned', '>', 0)
            ->orderBy('total_resolved', 'desc')
            ->get();

        // Ticket productivity
        $ticketProductivity = Outage::select('status')
            ->selectRaw('COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('status')
            ->get();

        return view($this->view('reports.productivity'), compact(
            'teamProductivity', 'userProductivity', 'ticketProductivity', 'startDate', 'endDate'
        ));
    }

    /**
     * Generate SLA breakdown report.
     */
    public function slaBreakdown(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        // Within SLA vs Outside SLA breakdown
        $withinSLA = Outage::where('sla_breached', false)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        $outsideSLA = Outage::where('sla_breached', true)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // SLA breakdown by priority
        $slaBreakdownByPriority = Outage::select('priority')
            ->selectRaw('COUNT(CASE WHEN sla_breached = 0 THEN 1 END) as within_sla')
            ->selectRaw('COUNT(CASE WHEN sla_breached = 1 THEN 1 END) as outside_sla')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('priority')
            ->get();

        // SLA breakdown by team
        $slaBreakdownByTeam = Outage::select('team_types.type_name as team_name')
            ->selectRaw('COUNT(CASE WHEN outages.sla_breached = 0 THEN 1 END) as within_sla')
            ->selectRaw('COUNT(CASE WHEN outages.sla_breached = 1 THEN 1 END) as outside_sla')
            ->leftJoin('team_types', 'outages.assigned_team_id', '=', 'team_types.id')
            ->leftJoin('departments', 'team_types.department_id', '=', 'departments.id')
            ->where('departments.department_name', 'Infrastructure')
            ->whereBetween('outages.created_at', [$startDate, $endDate])
            ->groupBy('team_types.id', 'team_types.type_name')
            ->having(DB::raw('within_sla + outside_sla'), '>', 0)
            ->orderBy('outside_sla', 'desc')
            ->get();

        // Monthly SLA trend
        $monthlySLATrend = Outage::select(DB::raw('YEAR(created_at) as year, MONTH(created_at) as month'))
            ->selectRaw('COUNT(CASE WHEN sla_breached = 0 THEN 1 END) as within_sla')
            ->selectRaw('COUNT(CASE WHEN sla_breached = 1 THEN 1 END) as outside_sla')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        return view($this->view('reports.sla-breakdown'), compact(
            'withinSLA', 'outsideSLA', 'slaBreakdownByPriority', 'slaBreakdownByTeam',
            'monthlySLATrend', 'startDate', 'endDate'
        ));
    }

    /**
     * Export reports to Excel.
     */
    public function export(Request $request)
    {
        $reportType = $request->get('type', 'all');
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        // This would integrate with Laravel Excel package
        // For now, return a CSV export simulation

        $filename = "outage_report_{$reportType}_{$startDate}_to_{$endDate}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($reportType, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            
            switch ($reportType) {
                case 'sla':
                    $this->exportSLAReport($file, $startDate, $endDate);
                    break;
                case 'productivity':
                    $this->exportProductivityReport($file, $startDate, $endDate);
                    break;
                case 'breakdown':
                    $this->exportBreakdownReport($file, $startDate, $endDate);
                    break;
                default:
                    $this->exportAllReports($file, $startDate, $endDate);
                    break;
            }
            
            fclose($file);
        };

        // Send notification for download
        $reportName = ucfirst($reportType) . ' Report';
        $this->notificationService->setToastNotification('info', $reportName . ' downloaded successfully');

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export SLA report to CSV.
     */
    private function exportSLAReport($file, $startDate, $endDate)
    {
        fputcsv($file, ['SLA Compliance Report', "Period: {$startDate} to {$endDate}"]);
        fputcsv($file, []); // Empty row
        
        fputcsv($file, ['Priority', 'Total Outages', 'SLA Breached', 'Compliance Rate (%)']);
        
        $slaByPriority = Outage::select('priority')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COUNT(CASE WHEN sla_breached = 1 THEN 1 END) as breached')
            ->selectRaw('ROUND((COUNT(*) - COUNT(CASE WHEN sla_breached = 1 THEN 1 END)) / COUNT(*) * 100, 2) as compliance_rate')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('priority')
            ->get();

        foreach ($slaByPriority as $row) {
            fputcsv($file, [
                $row->priority,
                $row->total,
                $row->breached,
                $row->compliance_rate
            ]);
        }
    }

    /**
     * Export productivity report to CSV.
     */
    private function exportProductivityReport($file, $startDate, $endDate)
    {
        fputcsv($file, ['Team Productivity Report', "Period: {$startDate} to {$endDate}"]);
        fputcsv($file, []); // Empty row
        
        fputcsv($file, ['Team', 'Total Outages', 'Resolved', 'Closed', 'Avg Resolution Time (minutes)']);
        
        $teamProductivity = \App\Models\TeamType::select('team_types.type_name as name')
            ->selectRaw('COUNT(outages.id) as total_outages')
            ->selectRaw('COUNT(CASE WHEN outages.status = "Resolved" THEN 1 END) as resolved_outages')
            ->selectRaw('COUNT(CASE WHEN outages.status = "Closed" THEN 1 END) as closed_outages')
            ->selectRaw('AVG(CASE WHEN outages.status = "Resolved" AND outages.end_time IS NOT NULL 
                THEN TIMESTAMPDIFF(MINUTE, outages.start_time, outages.end_time) END) as avg_resolution_time')
            ->leftJoin('outages', 'team_types.id', '=', 'outages.assigned_team_id')
            ->leftJoin('departments', 'team_types.department_id', '=', 'departments.id')
            ->where('departments.department_name', 'Infrastructure')
            ->where('team_types.status', 'Active')
            ->whereBetween('outages.created_at', [$startDate, $endDate])
            ->groupBy('team_types.id', 'team_types.type_name')
            ->having('total_outages', '>', 0)
            ->get();

        foreach ($teamProductivity as $row) {
            fputcsv($file, [
                $row->name,
                $row->total_outages,
                $row->resolved_outages,
                $row->closed_outages,
                round($row->avg_resolution_time ?? 0, 2)
            ]);
        }
    }

    /**
     * Export breakdown report to CSV.
     */
    private function exportBreakdownReport($file, $startDate, $endDate)
    {
        fputcsv($file, ['SLA Breakdown Report', "Period: {$startDate} to {$endDate}"]);
        fputcsv($file, []); // Empty row
        
        fputcsv($file, ['Priority', 'Within SLA', 'Outside SLA']);
        
        $slaBreakdownByPriority = Outage::select('priority')
            ->selectRaw('COUNT(CASE WHEN sla_breached = 0 THEN 1 END) as within_sla')
            ->selectRaw('COUNT(CASE WHEN sla_breached = 1 THEN 1 END) as outside_sla')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('priority')
            ->get();

        foreach ($slaBreakdownByPriority as $row) {
            fputcsv($file, [
                $row->priority,
                $row->within_sla,
                $row->outside_sla
            ]);
        }
    }

    /**
     * Generate trends analysis report.
     */
    public function trends(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        // Trend analysis logic here
        $trendsData = [];
        
        return view($this->view('reports.trends'), compact('trendsData', 'startDate', 'endDate'));
    }

    /**
     * Generate customer impact report.
     */
    public function impact(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        // Customer impact analysis using merged outages structure
        $impactData = [
            // Impact level breakdown
            'by_impact_level' => Outage::select('impact')
                ->selectRaw('COUNT(*) as count')
                ->selectRaw('SUM(COALESCE(total_customers_affected, 0)) as total_customers')
                ->selectRaw('AVG(CASE WHEN status = "Resolved" AND end_time IS NOT NULL 
                    THEN TIMESTAMPDIFF(HOUR, start_time, end_time) END) as avg_duration')
                ->whereBetween('start_time', [$startDate, $endDate])
                ->groupBy('impact')
                ->orderByRaw('FIELD(impact, "Critical", "High", "Medium", "Low")')
                ->get(),
                
            // Ticket type impact breakdown
            'by_ticket_type' => Outage::select('ticket_type')
                ->selectRaw('COUNT(*) as count')
                ->selectRaw('SUM(COALESCE(total_customers_affected, 0)) as total_customers')
                ->selectRaw('AVG(CASE WHEN status = "Resolved" AND end_time IS NOT NULL 
                    THEN TIMESTAMPDIFF(HOUR, start_time, end_time) END) as avg_duration')
                ->whereBetween('start_time', [$startDate, $endDate])
                ->groupBy('ticket_type')
                ->get(),
                
            // Top outages by customer impact
            'top_by_customers' => Outage::select('ticket_number', 'title', 'impact', 'total_customers_affected', 'start_time', 'end_time', 'status')
                ->whereBetween('start_time', [$startDate, $endDate])
                ->whereNotNull('total_customers_affected')
                ->orderBy('total_customers_affected', 'desc')
                ->limit(10)
                ->get(),
                
            // Summary metrics
            'summary' => [
                'total_outages' => Outage::whereBetween('start_time', [$startDate, $endDate])->count(),
                'total_customers_affected' => Outage::whereBetween('start_time', [$startDate, $endDate])
                    ->whereNotNull('total_customers_affected')
                    ->sum('total_customers_affected'),
                'avg_customers_per_outage' => Outage::whereBetween('start_time', [$startDate, $endDate])
                    ->whereNotNull('total_customers_affected')
                    ->avg('total_customers_affected'),
                'critical_outages' => Outage::whereBetween('start_time', [$startDate, $endDate])
                    ->where('impact', 'Critical')
                    ->count()
            ]
        ];
        
        return view($this->view('reports.impact'), compact('impactData', 'startDate', 'endDate'));
    }

    /**
     * Generate root cause analysis report.
     */
    public function rootCause(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        // Root cause analysis logic here
        $rootCauseData = [];
        
        return view($this->view('reports.root-cause'), compact('rootCauseData', 'startDate', 'endDate'));
    }

    /**
     * Export SLA report to CSV.
     */
    public function slaExport(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        $filename = 'outage_sla_report_' . $startDate . '_to_' . $endDate . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($startDate, $endDate) {
            $file = fopen('php://output', 'w');
            $this->exportSLAReport($file, $startDate, $endDate);
            fclose($file);
        };
        
        // Send notification for download
        $this->notificationService->setToastNotification('info', 'SLA Report downloaded successfully');
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export productivity report to CSV.
     */
    public function productivityExport(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        $filename = 'outage_productivity_report_' . $startDate . '_to_' . $endDate . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($startDate, $endDate) {
            $file = fopen('php://output', 'w');
            $this->exportProductivityReport($file, $startDate, $endDate);
            fclose($file);
        };
        
        // Send notification for download
        $this->notificationService->setToastNotification('info', 'Productivity Report downloaded successfully');
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export SLA breakdown report to CSV.
     */
    public function slaBreakdownExport(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        $filename = 'outage_sla_breakdown_report_' . $startDate . '_to_' . $endDate . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($startDate, $endDate) {
            $file = fopen('php://output', 'w');
            $this->exportBreakdownReport($file, $startDate, $endDate);
            fclose($file);
        };
        
        // Send notification for download
        $this->notificationService->setToastNotification('info', 'SLA Breakdown Report downloaded successfully');
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export trends report to CSV.
     */
    public function trendsExport(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        $filename = 'outage_trends_report_' . $startDate . '_to_' . $endDate . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($startDate, $endDate) {
            $file = fopen('php://output', 'w');
            $this->exportTrendsReport($file, $startDate, $endDate);
            fclose($file);
        };
        
        // Send notification for download
        $this->notificationService->setToastNotification('info', 'Trends Report downloaded successfully');
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export impact report to CSV.
     */
    public function impactExport(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        $filename = 'outage_impact_report_' . $startDate . '_to_' . $endDate . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($startDate, $endDate) {
            $file = fopen('php://output', 'w');
            $this->exportImpactReport($file, $startDate, $endDate);
            fclose($file);
        };
        
        // Send notification for download
        $this->notificationService->setToastNotification('info', 'Impact Report downloaded successfully');
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export root cause report to CSV.
     */
    public function rootCauseExport(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        $filename = 'outage_root_cause_report_' . $startDate . '_to_' . $endDate . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($startDate, $endDate) {
            $file = fopen('php://output', 'w');
            $this->exportRootCauseReport($file, $startDate, $endDate);
            fclose($file);
        };
        
        // Send notification for download
        $this->notificationService->setToastNotification('info', 'Root Cause Report downloaded successfully');
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export trends report to CSV.
     */
    private function exportTrendsReport($file, $startDate, $endDate)
    {
        fputcsv($file, ['Trends Analysis Report', "Period: {$startDate} to {$endDate}"]);
        fputcsv($file, []); // Empty row
        
        fputcsv($file, ['Month', 'Total Outages', 'Resolved', 'Average Resolution Time (hours)']);
        
        // Monthly trend data
        $monthlyData = Outage::selectRaw('YEAR(start_time) as year, MONTH(start_time) as month')
            ->selectRaw('COUNT(*) as total_outages')
            ->selectRaw('COUNT(CASE WHEN status = "Resolved" THEN 1 END) as resolved_outages')
            ->selectRaw('AVG(CASE WHEN status = "Resolved" AND end_time IS NOT NULL 
                THEN TIMESTAMPDIFF(HOUR, start_time, end_time) END) as avg_resolution_time')
            ->whereBetween('start_time', [$startDate, $endDate])
            ->groupByRaw('YEAR(start_time), MONTH(start_time)')
            ->orderByRaw('YEAR(start_time), MONTH(start_time)')
            ->get();

        foreach ($monthlyData as $row) {
            $monthName = date('F Y', mktime(0, 0, 0, $row->month, 1, $row->year));
            fputcsv($file, [
                $monthName,
                $row->total_outages,
                $row->resolved_outages,
                round($row->avg_resolution_time ?? 0, 2)
            ]);
        }
    }

    /**
     * Export impact report to CSV.
     */
    private function exportImpactReport($file, $startDate, $endDate)
    {
        fputcsv($file, ['Customer Impact Report', "Period: {$startDate} to {$endDate}"]);
        fputcsv($file, []); // Empty row
        
        fputcsv($file, ['Impact Level', 'Total Outages', 'Total Customers Affected', 'Average Duration (hours)']);
    
    // Impact analysis using merged outages structure
    $impactData = Outage::select('impact')
        ->selectRaw('COUNT(*) as total_outages')
        ->selectRaw('SUM(COALESCE(total_customers_affected, 0)) as total_customers')
        ->selectRaw('AVG(CASE WHEN status = "Resolved" AND end_time IS NOT NULL 
            THEN TIMESTAMPDIFF(HOUR, start_time, end_time) END) as avg_duration')
        ->whereBetween('start_time', [$startDate, $endDate])
        ->groupBy('impact')
        ->orderByRaw('FIELD(impact, "Critical", "High", "Medium", "Low")')
        ->get();

    foreach ($impactData as $row) {
        fputcsv($file, [
            $row->impact,
            $row->total_outages,
            number_format($row->total_customers ?? 0),
            round($row->avg_duration ?? 0, 2)
        ]);
    }
    }

    /**
     * Export root cause report to CSV.
     */
    private function exportRootCauseReport($file, $startDate, $endDate)
    {
        fputcsv($file, ['Root Cause Analysis Report', "Period: {$startDate} to {$endDate}"]);
        fputcsv($file, []); // Empty row
        
        fputcsv($file, ['Root Cause', 'Occurrences', 'Percentage', 'Average Resolution Time (hours)']);
        
        $totalOutages = Outage::whereBetween('start_time', [$startDate, $endDate])->count();
        
        $rootCauseData = Outage::select('root_cause')
            ->selectRaw('COUNT(*) as occurrences')
            ->selectRaw('AVG(CASE WHEN status = "Resolved" AND end_time IS NOT NULL 
                THEN TIMESTAMPDIFF(HOUR, start_time, end_time) END) as avg_resolution_time')
            ->whereBetween('start_time', [$startDate, $endDate])
            ->whereNotNull('root_cause')
            ->groupBy('root_cause')
            ->orderBy('occurrences', 'desc')
            ->get();

        foreach ($rootCauseData as $row) {
            $percentage = $totalOutages > 0 ? round(($row->occurrences / $totalOutages) * 100, 2) : 0;
            fputcsv($file, [
                $row->root_cause,
                $row->occurrences,
                $percentage . '%',
                round($row->avg_resolution_time ?? 0, 2)
            ]);
        }
    }

    /**
     * Export all reports to CSV.
     */
    private function exportAllReports($file, $startDate, $endDate)
    {
        $this->exportSLAReport($file, $startDate, $endDate);
        fputcsv($file, []); // Empty row
        $this->exportProductivityReport($file, $startDate, $endDate);
        fputcsv($file, []); // Empty row
        $this->exportBreakdownReport($file, $startDate, $endDate);
        fputcsv($file, []); // Empty row
        $this->exportTrendsReport($file, $startDate, $endDate);
        fputcsv($file, []); // Empty row
        $this->exportImpactReport($file, $startDate, $endDate);
        fputcsv($file, []); // Empty row
        $this->exportRootCauseReport($file, $startDate, $endDate);
    }
}
