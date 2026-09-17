<?php

namespace Modules\Appointment\Http\Controllers;

use App\Models\SubDepartment;
use App\Models\SubTeamType;
use App\Models\Team;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Appointment\Models\Appointment;

class DashboardController extends Controller
{
    /**
     * Display the appointment dashboard.
     */
    public function index()
    {
        // Get all active sub-departments
        $subDepartments = SubDepartment::where('sub_department_status', 'Active')->get();

        $dashboardData = [];

        foreach ($subDepartments as $subDepartment) {
            // Get metrics for this sub-department
            $metrics = $this->getSubDepartmentMetrics($subDepartment->id);
            $dashboardData[$subDepartment->id] = [
                'name' => $subDepartment->sub_department_name,
                'metrics' => $metrics,
            ];
        }

        // Get sub team metrics
        $subTeams = SubTeamType::where('sub_type_status', 'Active')->get();
        $subTeamData = [];

        foreach ($subTeams as $subTeam) {
            $metrics = $this->getSubTeamMetrics($subTeam->id);
            $subTeamData[$subTeam->id] = [
                'name' => $subTeam->sub_type_name,
                'metrics' => $metrics,
            ];
        }

        // Get assigned team metrics
        $assignedTeams = Team::where('status', 'Active')->get();
        $assignedTeamData = [];

        foreach ($assignedTeams as $team) {
            $metrics = $this->getAssignedTeamMetrics($team->id);
            $assignedTeamData[$team->id] = [
                'name' => $team->team_name,
                'metrics' => $metrics,
            ];
        }

        // Get overall metrics
        $overallMetrics = $this->getOverallMetrics();

        // Open tickets aged since raised, by region then sub category
        $openTicketsAging = $this->getOpenTicketsAgingByRegionAndSubCategory();

        return view('appointment::dashboard.index', compact(
            'dashboardData',
            'overallMetrics',
            'subDepartments',
            'subTeamData',
            'subTeams',
            'assignedTeamData',
            'assignedTeams',
            'openTicketsAging'
        ));
    }

    /**
     * Open (not closed) appointments broken down by region, then by sub
     * category within each region, aged into fixed hour bands since
     * created_at. Bands share the same 3/6/9/12/15/18/21/24/48/72(+) hour
     * boundaries used by the Region/Open Tickets reports (see
     * App\Traits\BucketsTicketAge) but are rendered here as a cross-tab
     * (one column per band) rather than a single labeled range per row,
     * matching the pivot-table layout this section is meant to reproduce.
     */
    private function getOpenTicketsAgingByRegionAndSubCategory(): array
    {
        $closedStatuses = ['Completed', 'Closed', 'Scheduled-Closed'];
        $boundaries = [3, 6, 9, 12, 15, 18, 21, 24, 48, 72];
        $bucketCount = count($boundaries) + 1; // +1 for "> 72 hrs"

        $bucketIndexFor = function (float $hours) use ($boundaries) {
            foreach ($boundaries as $i => $boundary) {
                if ($hours <= $boundary) {
                    return $i;
                }
            }

            return count($boundaries);
        };

        $rows = DB::table('appointments')
            ->leftJoin('regions', 'appointments.region_id', '=', 'regions.id')
            ->leftJoin('subcategories', 'appointments.sub_category_id', '=', 'subcategories.id')
            ->select('appointments.created_at', 'regions.name as region_name', 'subcategories.sub_category_name')
            ->whereNotIn('appointments.status', $closedStatuses)
            ->get();

        $regions = [];
        $grandTotal = ['total' => 0, 'buckets' => array_fill(0, $bucketCount, 0)];

        foreach ($rows as $row) {
            $regionName = $row->region_name ?? 'Unassigned';
            $subCategoryName = $row->sub_category_name ?? 'Uncategorized';
            $hours = \Carbon\Carbon::parse($row->created_at)->diffInHours(now());
            $bucketIndex = $bucketIndexFor($hours);

            if (! isset($regions[$regionName])) {
                $regions[$regionName] = [
                    'total' => 0,
                    'buckets' => array_fill(0, $bucketCount, 0),
                    'sub_categories' => [],
                ];
            }
            if (! isset($regions[$regionName]['sub_categories'][$subCategoryName])) {
                $regions[$regionName]['sub_categories'][$subCategoryName] = [
                    'total' => 0,
                    'buckets' => array_fill(0, $bucketCount, 0),
                ];
            }

            $regions[$regionName]['total']++;
            $regions[$regionName]['buckets'][$bucketIndex]++;
            $regions[$regionName]['sub_categories'][$subCategoryName]['total']++;
            $regions[$regionName]['sub_categories'][$subCategoryName]['buckets'][$bucketIndex]++;
            $grandTotal['total']++;
            $grandTotal['buckets'][$bucketIndex]++;
        }

        // Largest-backlog region first, matching this dashboard's existing
        // convention of surfacing where the problem is worst.
        uasort($regions, fn ($a, $b) => $b['total'] <=> $a['total']);

        foreach ($regions as &$region) {
            uasort($region['sub_categories'], fn ($a, $b) => $b['total'] <=> $a['total']);
        }
        unset($region);

        return [
            'boundaries' => $boundaries,
            'regions' => $regions,
            'grand_total' => $grandTotal,
        ];
    }

    /**
     * Get metrics for a specific sub-department.
     */
    private function getSubDepartmentMetrics($subDepartmentId)
    {
        // Today's metrics with SLA = 2 hours
        $todayReceived = Appointment::where('sub_department_id', $subDepartmentId)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        $todayClosed = Appointment::where('sub_department_id', $subDepartmentId)
            ->whereIn('status', ['Scheduled-Closed', 'Completed', 'Closed'])
            ->whereDate('completed_date', now()->toDateString())
            ->count();

        // Within SLA (2 hours)
        $todayClosedWithinSla = Appointment::where('sub_department_id', $subDepartmentId)
            ->whereIn('status', ['Scheduled-Closed', 'Completed', 'Closed'])
            ->whereDate('completed_date', now()->toDateString())
            ->whereRaw('TIMESTAMPDIFF(HOUR, created_at, completed_date) <= 2')
            ->count();

        // Outside SLA (2 hours)
        $todayClosedOutsideSla = Appointment::where('sub_department_id', $subDepartmentId)
            ->whereIn('status', ['Scheduled-Closed', 'Completed', 'Closed'])
            ->whereDate('completed_date', now()->toDateString())
            ->whereRaw('TIMESTAMPDIFF(HOUR, created_at, completed_date) > 2')
            ->count();

        // Calculate backlog (open appointments from previous days)
        $backlog = Appointment::where('sub_department_id', $subDepartmentId)
            ->whereNotIn('status', ['Scheduled-Closed', 'Completed', 'Closed'])
            ->whereDate('created_at', '<', now()->toDateString())
            ->count();

        // Calculate SLA compliance percentage
        $slaCompliancePercentage = $todayClosed > 0
            ? round(($todayClosedWithinSla / $todayClosed) * 100, 2)
            : 0;

        return [
            'today_received' => $todayReceived,
            'today_closed' => $todayClosed,
            'today_closed_within_sla' => $todayClosedWithinSla,
            'today_closed_outside_sla' => $todayClosedOutsideSla,
            'backlog' => $backlog,
            'sla_compliance_percentage' => $slaCompliancePercentage,
        ];
    }

    /**
     * Get metrics for a specific sub team.
     */
    private function getSubTeamMetrics($subTeamId)
    {
        // Today's metrics with SLA = 2 hours
        $todayReceived = Appointment::where('sub_team_type_id', $subTeamId)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        $todayClosed = Appointment::where('sub_team_type_id', $subTeamId)
            ->whereIn('status', ['Scheduled-Closed', 'Completed', 'Closed'])
            ->whereDate('completed_date', now()->toDateString())
            ->count();

        // Within SLA (2 hours)
        $todayClosedWithinSla = Appointment::where('sub_team_type_id', $subTeamId)
            ->whereIn('status', ['Scheduled-Closed', 'Completed', 'Closed'])
            ->whereDate('completed_date', now()->toDateString())
            ->whereRaw('TIMESTAMPDIFF(HOUR, created_at, completed_date) <= 2')
            ->count();

        // Outside SLA (2 hours)
        $todayClosedOutsideSla = Appointment::where('sub_team_type_id', $subTeamId)
            ->whereIn('status', ['Scheduled-Closed', 'Completed', 'Closed'])
            ->whereDate('completed_date', now()->toDateString())
            ->whereRaw('TIMESTAMPDIFF(HOUR, created_at, completed_date) > 2')
            ->count();

        // Calculate backlog (open appointments from previous days)
        $backlog = Appointment::where('sub_team_type_id', $subTeamId)
            ->whereNotIn('status', ['Scheduled-Closed', 'Completed', 'Closed'])
            ->whereDate('created_at', '<', now()->toDateString())
            ->count();

        // Calculate SLA compliance percentage
        $slaCompliancePercentage = $todayClosed > 0
            ? round(($todayClosedWithinSla / $todayClosed) * 100, 2)
            : 0;

        return [
            'today_received' => $todayReceived,
            'today_closed' => $todayClosed,
            'today_closed_within_sla' => $todayClosedWithinSla,
            'today_closed_outside_sla' => $todayClosedOutsideSla,
            'backlog' => $backlog,
            'sla_compliance_percentage' => $slaCompliancePercentage,
        ];
    }

    /**
     * Get metrics for a specific assigned team.
     */
    private function getAssignedTeamMetrics($teamId)
    {
        // Today's metrics with SLA = 2 hours
        $todayReceived = Appointment::where('assigned_team_id', $teamId)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        $todayClosed = Appointment::where('assigned_team_id', $teamId)
            ->whereIn('status', ['Scheduled-Closed', 'Completed', 'Closed'])
            ->whereDate('completed_date', now()->toDateString())
            ->count();

        // Within SLA (2 hours)
        $todayClosedWithinSla = Appointment::where('assigned_team_id', $teamId)
            ->whereIn('status', ['Scheduled-Closed', 'Completed', 'Closed'])
            ->whereDate('completed_date', now()->toDateString())
            ->whereRaw('TIMESTAMPDIFF(HOUR, created_at, completed_date) <= 2')
            ->count();

        // Outside SLA (2 hours)
        $todayClosedOutsideSla = Appointment::where('assigned_team_id', $teamId)
            ->whereIn('status', ['Scheduled-Closed', 'Completed', 'Closed'])
            ->whereDate('completed_date', now()->toDateString())
            ->whereRaw('TIMESTAMPDIFF(HOUR, created_at, completed_date) > 2')
            ->count();

        // Calculate backlog (open appointments from previous days)
        $backlog = Appointment::where('assigned_team_id', $teamId)
            ->whereNotIn('status', ['Scheduled-Closed', 'Completed', 'Closed'])
            ->whereDate('created_at', '<', now()->toDateString())
            ->count();

        // Calculate SLA compliance percentage
        $slaCompliancePercentage = $todayClosed > 0
            ? round(($todayClosedWithinSla / $todayClosed) * 100, 2)
            : 0;

        return [
            'today_received' => $todayReceived,
            'today_closed' => $todayClosed,
            'today_closed_within_sla' => $todayClosedWithinSla,
            'today_closed_outside_sla' => $todayClosedOutsideSla,
            'backlog' => $backlog,
            'sla_compliance_percentage' => $slaCompliancePercentage,
        ];
    }

    /**
     * Get overall metrics across all sub-departments.
     */
    private function getOverallMetrics()
    {
        // Today's metrics with SLA = 2 hours, TAT = 12 hours
        $todayReceived = Appointment::whereDate('created_at', now()->toDateString())->count();

        $todayClosed = Appointment::whereIn('status', ['Scheduled-Closed', 'Completed', 'Closed'])
            ->whereDate('completed_date', now()->toDateString())
            ->count();

        // Within SLA (2 hours)
        $todayClosedWithinSla = Appointment::whereIn('status', ['Scheduled-Closed', 'Completed', 'Closed'])
            ->whereDate('completed_date', now()->toDateString())
            ->whereRaw('TIMESTAMPDIFF(HOUR, created_at, completed_date) <= 2')
            ->count();

        // Outside SLA (2 hours)
        $todayClosedOutsideSla = Appointment::whereIn('status', ['Scheduled-Closed', 'Completed', 'Closed'])
            ->whereDate('completed_date', now()->toDateString())
            ->whereRaw('TIMESTAMPDIFF(HOUR, created_at, completed_date) > 2')
            ->count();

        // Calculate backlog (open appointments from previous days)
        $backlog = Appointment::whereNotIn('status', ['Scheduled-Closed', 'Completed', 'Closed'])
            ->whereDate('created_at', '<', now()->toDateString())
            ->count();

        // Calculate SLA compliance percentage
        $slaCompliancePercentage = $todayClosed > 0
            ? round(($todayClosedWithinSla / $todayClosed) * 100, 2)
            : 0;

        // Get sub team with highest backlog
        $highestBacklogTeam = DB::table('appointments')
            ->select('sub_team_type_id', DB::raw('COUNT(*) as backlog_count'))
            ->whereNotIn('status', ['Scheduled-Closed', 'Completed', 'Closed'])
            ->whereDate('created_at', '<', now()->toDateString())
            ->whereNotNull('sub_team_type_id')
            ->groupBy('sub_team_type_id')
            ->orderByDesc('backlog_count')
            ->first();

        $highestBacklogTeamName = null;
        if ($highestBacklogTeam) {
            $subTeam = SubTeamType::find($highestBacklogTeam->sub_team_type_id);
            $highestBacklogTeamName = $subTeam ? $subTeam->sub_type_name : 'Unknown';
        }

        // TAT metrics (12 hours)
        $withinTat = Appointment::whereIn('status', ['Scheduled-Closed', 'Completed', 'Closed'])
            ->whereDate('completed_date', now()->toDateString())
            ->whereRaw('TIMESTAMPDIFF(HOUR, created_at, completed_date) <= 12')
            ->count();

        $outsideTat = Appointment::whereIn('status', ['Scheduled-Closed', 'Completed', 'Closed'])
            ->whereDate('completed_date', now()->toDateString())
            ->whereRaw('TIMESTAMPDIFF(HOUR, created_at, completed_date) > 12')
            ->count();

        $tatCompliancePercentage = $todayClosed > 0
            ? round(($withinTat / $todayClosed) * 100, 2)
            : 0;

        return [
            'today_received' => $todayReceived,
            'today_closed' => $todayClosed,
            'today_closed_within_sla' => $todayClosedWithinSla,
            'today_closed_outside_sla' => $todayClosedOutsideSla,
            'backlog' => $backlog,
            'sla_compliance_percentage' => $slaCompliancePercentage,
            'highest_backlog_dept' => $highestBacklogTeamName,
            'within_tat' => $withinTat,
            'outside_tat' => $outsideTat,
            'tat_compliance_percentage' => $tatCompliancePercentage,
        ];
    }
}
