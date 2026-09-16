<?php

namespace Modules\Appointment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Appointment\Models\Appointment;
use App\Models\SubDepartment;
use App\Models\SubTeamType;
use App\Models\Team;
use Illuminate\Support\Facades\DB;

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
                'metrics' => $metrics
            ];
        }

        // Get sub team metrics
        $subTeams = SubTeamType::where('sub_type_status', 'Active')->get();
        $subTeamData = [];

        foreach ($subTeams as $subTeam) {
            $metrics = $this->getSubTeamMetrics($subTeam->id);
            $subTeamData[$subTeam->id] = [
                'name' => $subTeam->sub_type_name,
                'metrics' => $metrics
            ];
        }

        // Get assigned team metrics
        $assignedTeams = Team::where('status', 'Active')->get();
        $assignedTeamData = [];

        foreach ($assignedTeams as $team) {
            $metrics = $this->getAssignedTeamMetrics($team->id);
            $assignedTeamData[$team->id] = [
                'name' => $team->team_name,
                'metrics' => $metrics
            ];
        }

        // Get overall metrics
        $overallMetrics = $this->getOverallMetrics();

        return view('appointment::dashboard.index', compact(
            'dashboardData',
            'overallMetrics',
            'subDepartments',
            'subTeamData',
            'subTeams',
            'assignedTeamData',
            'assignedTeams'
        ));
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
            'sla_compliance_percentage' => $slaCompliancePercentage
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
            'sla_compliance_percentage' => $slaCompliancePercentage
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
            'sla_compliance_percentage' => $slaCompliancePercentage
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
            'tat_compliance_percentage' => $tatCompliancePercentage
        ];
    }
}
