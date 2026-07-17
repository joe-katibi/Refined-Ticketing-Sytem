<?php

namespace Modules\Outages\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Modules\Outages\Models\Outage;
use Modules\Outages\Models\Olt;
use App\Models\Team;
use App\Models\TeamType;

class OutageDashboardController extends OutagesController
{
    /**
     * Display the outage dashboard.
     */
    public function index(Request $request)
    {
        // Date range for filtering (default to last 30 days)
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        // Basic metrics
        $totalOutages = Outage::whereBetween('created_at', [$startDate, $endDate])->count();
        $activeOutages = Outage::where('status', '!=', 'Closed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        $resolvedOutages = Outage::where('status', 'Resolved')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        $criticalOutages = Outage::where('priority', 'Critical')
            ->where('status', '!=', 'Closed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // SLA metrics
        $slaBreachedOutages = Outage::where('sla_breached', true)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        $slaComplianceRate = $totalOutages > 0 ?
            round((($totalOutages - $slaBreachedOutages) / $totalOutages) * 100, 2) : 100;

        // Customer Impact metrics
        $totalCustomersAffected = Outage::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('total_customers_affected')
            ->sum('total_customers_affected');
        $avgCustomersPerOutage = $totalOutages > 0 ?
            round($totalCustomersAffected / $totalOutages, 0) : 0;

        // Ticket Type metrics (replacing old OutageTicket metrics)
        $regularOutages = Outage::where('ticket_type', 'regular')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        $emergencyOutages = Outage::where('ticket_type', 'emergency')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        $plannedMaintenanceOutages = Outage::where('ticket_type', 'planned_maintenance')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Outages by status
        $outagesByStatus = Outage::select('status', DB::raw('count(*) as count'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('status')
            ->get();

        // Outages by priority
        $outagesByPriority = Outage::select('priority', DB::raw('count(*) as count'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('priority')
            ->get();

        // Outages by team
        $outagesByTeam = Outage::select('team_types.type_name as team_name', DB::raw('count(outages.id) as count'))
            ->leftJoin('team_types', 'outages.assigned_team_id', '=', 'team_types.id')
            ->leftJoin('departments', 'team_types.department_id', '=', 'departments.id')
            ->where('departments.department_name', 'Infrastructure')
            ->whereBetween('outages.created_at', [$startDate, $endDate])
            ->groupBy('team_types.id', 'team_types.type_name')
            ->orderBy('count', 'desc')
            ->get();

        // Recent outages
        $recentOutages = Outage::with(['assignedTeam', 'assignee'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // MTTR (Mean Time To Resolution) calculation
        $resolvedOutagesWithTime = Outage::where('status', 'Resolved')
            ->whereNotNull('end_time')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $mttr = 0;
        if ($resolvedOutagesWithTime->count() > 0) {
            $totalResolutionTime = $resolvedOutagesWithTime->sum(function ($outage) {
                return $outage->start_time->diffInMinutes($outage->end_time);
            });
            $mttr = round($totalResolutionTime / $resolvedOutagesWithTime->count(), 2);
        }

        // Trend data for charts (last 7 days)
        $trendData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayOutages = Outage::whereDate('created_at', $date->format('Y-m-d'))->count();
            $dayResolved = Outage::where('status', 'Resolved')
                ->whereDate('end_time', $date->format('Y-m-d'))
                ->count();

            $trendData[] = [
                'date' => $date->format('M d'),
                'outages' => $dayOutages,
                'resolved' => $dayResolved,
            ];
        }

        // Top impacted areas
        $impactedAreas = Outage::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('impacted_areas')
            ->get()
            ->pluck('impacted_areas')
            ->flatten()
            ->countBy()
            ->sortDesc()
            ->take(10);

        // Top impacted services
        $impactedServices = Outage::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('impacted_services')
            ->get()
            ->pluck('impacted_services')
            ->flatten()
            ->countBy()
            ->sortDesc()
            ->take(10);

        // Team performance
        $teamPerformance = \App\Models\TeamType::select('team_types.type_name as name')
            ->selectRaw('COUNT(outages.id) as total_outages')
            ->selectRaw('COUNT(CASE WHEN outages.status = "Resolved" THEN 1 END) as resolved_outages')
            ->selectRaw('AVG(CASE WHEN outages.status = "Resolved" AND outages.end_time IS NOT NULL
                THEN TIMESTAMPDIFF(MINUTE, outages.start_time, outages.end_time) END) as avg_resolution_time')
            ->leftJoin('outages', 'team_types.id', '=', 'outages.assigned_team_id')
            ->leftJoin('departments', 'team_types.department_id', '=', 'departments.id')
            ->where('departments.department_name', 'Infrastructure')
            ->where('team_types.status', 'Active')
            ->whereBetween('outages.created_at', [$startDate, $endDate])
            ->groupBy('team_types.id', 'team_types.type_name')
            ->having('total_outages', '>', 0)
            ->orderBy('total_outages', 'desc')
            ->get();

        return view($this->view('dashboard'), compact(
            'totalOutages', 'activeOutages', 'resolvedOutages', 'criticalOutages',
            'slaBreachedOutages', 'slaComplianceRate', 'totalCustomersAffected', 'avgCustomersPerOutage',
            'regularOutages', 'emergencyOutages', 'plannedMaintenanceOutages', 'outagesByStatus', 'outagesByPriority', 'outagesByTeam',
            'recentOutages', 'mttr', 'trendData', 'impactedAreas', 'impactedServices',
            'teamPerformance', 'startDate', 'endDate'
        ));
    }

    /**
     * Get dashboard data for AJAX requests.
     */
    public function getData(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        $data = [
            'total_outages' => Outage::whereBetween('created_at', [$startDate, $endDate])->count(),
            'active_outages' => Outage::where('status', '!=', 'Closed')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),
            'resolved_outages' => Outage::where('status', 'Resolved')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),
            'critical_outages' => Outage::where('priority', 'Critical')
                ->where('status', '!=', 'Closed')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),
        ];

        return response()->json($data);
    }

    /**
     * Export dashboard data to Excel.
     */
    public function export(Request $request)
    {
        // This would integrate with Laravel Excel or similar package
        // For now, return a placeholder response
        return response()->json([
            'message' => 'Export functionality will be implemented with Laravel Excel package'
        ]);
    }
}
