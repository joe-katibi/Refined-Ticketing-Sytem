<?php

namespace Modules\Appointment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Appointment\Models\Appointment;
use App\Models\Team;
use App\Models\SubTeamType;
use App\Models\FinalReason;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Appointment\Exports\AppointmentReportExport;

class ReportsController extends Controller
{
  /**
   * Display the reports index page.
   */
  public function index()
  {
    return view('appointment::reports.index');
  }

  /**
   * Display SLA Report.
   */
  public function slaReport(Request $request)
  {
    $startDate = $request->get(
      'startDate',
      now()
        ->startOfMonth()
        ->format('Y-m-d')
    );
    $endDate = $request->get('endDate', now()->format('Y-m-d'));

    // Overall SLA metrics (2 hours SLA for appointments)
    $totalAppointments = Appointment::whereBetween('created_at', [$startDate, $endDate])->count();

    $closedAppointments = Appointment::whereBetween('created_at', [$startDate, $endDate])
      ->whereIn('status', ['Completed', 'Closed'])
      ->count();

    $withinSla = Appointment::whereBetween('created_at', [$startDate, $endDate])
      ->whereIn('status', ['Completed', 'Closed'])
      ->whereRaw('TIMESTAMPDIFF(HOUR, created_at, completed_date) <= 2')
      ->count();

    $outsideSla = Appointment::whereBetween('created_at', [$startDate, $endDate])
      ->whereIn('status', ['Completed', 'Closed'])
      ->whereRaw('TIMESTAMPDIFF(HOUR, created_at, completed_date) > 2')
      ->count();

    $slaCompliancePercentage = $closedAppointments > 0 ? round(($withinSla / $closedAppointments) * 100, 2) : 0;

    // Daily SLA breakdown
    $dailyMetrics = DB::table('appointments')
      ->select(
        DB::raw('DATE(created_at) as date'),
        DB::raw('COUNT(*) as total'),
        DB::raw('SUM(CASE WHEN status IN ("Completed", "Closed") THEN 1 ELSE 0 END) as closed'),
        DB::raw(
          'SUM(CASE WHEN status IN ("Completed", "Closed") AND TIMESTAMPDIFF(HOUR, created_at, completed_date) <= 2 THEN 1 ELSE 0 END) as within_sla'
        ),
        DB::raw(
          'SUM(CASE WHEN status IN ("Completed", "Closed") AND TIMESTAMPDIFF(HOUR, created_at, completed_date) > 2 THEN 1 ELSE 0 END) as outside_sla'
        )
      )
      ->whereBetween('created_at', [$startDate, $endDate])
      ->groupBy(DB::raw('DATE(created_at)'))
      ->orderBy('date')
      ->get();

    return view(
      'appointment::reports.sla',
      compact(
        'totalAppointments',
        'closedAppointments',
        'withinSla',
        'outsideSla',
        'slaCompliancePercentage',
        'dailyMetrics',
        'startDate',
        'endDate'
      )
    );
  }

  /**
   * Display Team Productivity Report.
   */
  public function teamProductivityReport(Request $request)
  {
    // Get date range from request or use default (last 30 days)
    $endDate = $request->input('endDate', date('Y-m-d'));
    $startDate = $request->input('startDate', date('Y-m-d', strtotime('-30 days')));

    // Format dates for database query
    $startDateTime = $startDate . ' 00:00:00';
    $endDateTime = $endDate . ' 23:59:59';

    // Get team productivity metrics - use left join to include all teams
    $teamMetrics = DB::table('team_types')
      ->leftJoin('appointments', function ($join) use ($startDateTime, $endDateTime) {
        $join
          ->on('appointments.assigned_team_id', '=', 'team_types.id')
          ->whereBetween('appointments.created_at', [$startDateTime, $endDateTime]);
      })
      ->select(
        'team_types.id',
        'team_types.type_name',
        DB::raw('COUNT(appointments.id) as total_assigned'),
        DB::raw("SUM(CASE WHEN appointments.status IN ('Completed', 'Closed') THEN 1 ELSE 0 END) as total_closed"),
        DB::raw(
          "SUM(CASE WHEN appointments.status IN ('Completed', 'Closed') AND TIMESTAMPDIFF(HOUR, appointments.created_at, appointments.completed_date) <= 2 THEN 1 ELSE 0 END) as closed_within_sla"
        ),
        DB::raw(
          "SUM(CASE WHEN appointments.status IN ('Completed', 'Closed') AND TIMESTAMPDIFF(HOUR, appointments.created_at, appointments.completed_date) > 2 THEN 1 ELSE 0 END) as closed_outside_sla"
        ),
        DB::raw(
          "ROUND(AVG(CASE WHEN appointments.status IN ('Completed', 'Closed') THEN TIMESTAMPDIFF(HOUR, appointments.created_at, appointments.completed_date) END), 2) as avg_resolution_time"
        )
      )
      ->groupBy('team_types.id', 'team_types.type_name')
      ->orderByDesc('total_closed')
      ->get();

    // Calculate SLA compliance percentage for each team
    $teamMetrics = $teamMetrics->map(function ($team) {
      $team->sla_compliance_percentage =
        $team->total_closed > 0 ? round(($team->closed_within_sla / $team->total_closed) * 100, 1) : 0;
      return $team;
    });

    return view('appointment::reports.team_productivity', compact('teamMetrics', 'startDate', 'endDate'));
  }

  /**
   * Display Sub Team Productivity Report.
   */
  public function subTeamProductivityReport(Request $request)
  {
    $startDate = $request->get(
      'startDate',
      now()
        ->startOfMonth()
        ->format('Y-m-d')
    );
    $endDate = $request->get('endDate', now()->format('Y-m-d'));

    // Format dates for SQL query
    $startDateTime = $startDate . ' 00:00:00';
    $endDateTime = $endDate . ' 23:59:59';

    // Get sub team productivity metrics
    $subTeamMetrics = DB::table('appointments')
      ->join('sub_team_types', 'appointments.sub_team_type_id', '=', 'sub_team_types.id')
      ->select(
        'sub_team_types.id',
        'sub_team_types.sub_type_name',
        DB::raw('COUNT(*) as total_assigned'),
        DB::raw('SUM(CASE WHEN appointments.status IN ("Completed", "Closed") THEN 1 ELSE 0 END) as total_closed'),
        DB::raw(
          'SUM(CASE WHEN appointments.status IN ("Completed", "Closed") AND TIMESTAMPDIFF(HOUR, appointments.created_at, appointments.completed_date) <= 2 THEN 1 ELSE 0 END) as closed_within_sla'
        ),
        DB::raw(
          'SUM(CASE WHEN appointments.status IN ("Completed", "Closed") AND TIMESTAMPDIFF(HOUR, appointments.created_at, appointments.completed_date) > 2 THEN 1 ELSE 0 END) as closed_outside_sla'
        ),
        DB::raw(
          'ROUND(AVG(CASE WHEN appointments.status IN ("Completed", "Closed") THEN TIMESTAMPDIFF(HOUR, appointments.created_at, appointments.completed_date) END), 2) as avg_resolution_time'
        )
      )
      ->whereBetween('appointments.created_at', [$startDateTime, $endDateTime])
      ->whereNotNull('appointments.sub_team_type_id')
      ->groupBy('sub_team_types.id', 'sub_team_types.sub_type_name')
      ->orderByDesc('total_closed')
      ->get();

    // Calculate SLA compliance percentage for each sub team
    foreach ($subTeamMetrics as $metric) {
      $metric->sla_compliance_percentage =
        $metric->total_closed > 0 ? round(($metric->closed_within_sla / $metric->total_closed) * 100, 2) : 0;
    }

    return view('appointment::reports.sub_team_productivity', compact('subTeamMetrics', 'startDate', 'endDate'));
  }

  /**
   * Display Assigned Team Productivity Report.
   */
  public function assignedTeamProductivityReport(Request $request)
  {
    $startDate = $request->get(
      'startDate',
      now()
        ->startOfMonth()
        ->format('Y-m-d')
    );
    $endDate = $request->get('endDate', now()->format('Y-m-d'));

    // Get assigned team productivity metrics
    $assignedTeamMetrics = DB::table('appointments')
      ->join('users', 'appointments.closed_by', '=', 'users.id')
      ->select(
        'users.id',
        'users.name',
        DB::raw('COUNT(*) as total_assigned'),
        DB::raw(
          'SUM(CASE WHEN appointments.status IN ("Completed", "Closed", "Scheduled-Closed") THEN 1 ELSE 0 END) as total_closed'
        ),
        DB::raw(
          'SUM(CASE WHEN appointments.status IN ("Completed", "Closed", "Scheduled-Closed") AND TIMESTAMPDIFF(HOUR, appointments.created_at, appointments.completed_date) <= 2 THEN 1 ELSE 0 END) as closed_within_sla'
        ),
        DB::raw(
          'SUM(CASE WHEN appointments.status IN ("Completed", "Closed", "Scheduled-Closed") AND TIMESTAMPDIFF(HOUR, appointments.created_at, appointments.completed_date) > 2 THEN 1 ELSE 0 END) as closed_outside_sla'
        ),
        DB::raw(
          'ROUND(AVG(CASE WHEN appointments.status IN ("Completed", "Closed", "Scheduled-Closed") THEN TIMESTAMPDIFF(HOUR, appointments.created_at, appointments.completed_date) END), 2) as avg_resolution_time'
        )
      )
      ->whereBetween('appointments.created_at', [$startDate, $endDate])
      ->whereNotNull('appointments.closed_by')
      ->whereIn('appointments.status', ['Completed', 'Closed', 'Scheduled-Closed'])
      ->groupBy('users.id', 'users.name')
      ->orderByDesc('total_closed')
      ->get();

    // Calculate SLA compliance percentage for each assigned user
    foreach ($assignedTeamMetrics as $metric) {
      $metric->sla_compliance_percentage =
        $metric->total_closed > 0 ? round(($metric->closed_within_sla / $metric->total_closed) * 100, 1) : 0;
    }

    return view(
      'appointment::reports.assigned_team_productivity',
      compact('assignedTeamMetrics', 'startDate', 'endDate')
    );
  }

  /**
   * Display Final Reason Report.
   */
  public function finalReasonReport(Request $request)
  {
    $startDate = $request->get(
      'startDate',
      now()
        ->startOfMonth()
        ->format('Y-m-d')
    );
    $endDate = $request->get('endDate', now()->format('Y-m-d'));

    // Get final reason metrics
    $finalReasonMetrics = DB::table('appointments')
      ->join('appointment_final_reasons', 'appointments.final_reason_id', '=', 'appointment_final_reasons.id')
      ->select(
        'appointment_final_reasons.id',
        'appointment_final_reasons.final_reason_name',
        DB::raw('COUNT(*) as total_appointments'),
        DB::raw(
          'SUM(CASE WHEN appointments.status IN ("Completed", "Closed", "Scheduled-Closed") THEN 1 ELSE 0 END) as closed_appointments'
        ),
        DB::raw(
          'SUM(CASE WHEN appointments.status NOT IN ("Completed", "Closed", "Scheduled-Closed") THEN 1 ELSE 0 END) as open_appointments'
        ),
        DB::raw(
          'SUM(CASE WHEN appointments.status IN ("Completed", "Closed", "Scheduled-Closed") AND TIMESTAMPDIFF(HOUR, appointments.created_at, appointments.completed_date) <= 2 THEN 1 ELSE 0 END) as closed_within_sla'
        ),
        DB::raw(
          'ROUND(AVG(CASE WHEN appointments.status IN ("Completed", "Closed", "Scheduled-Closed") THEN TIMESTAMPDIFF(HOUR, appointments.created_at, appointments.completed_date) END), 2) as avg_resolution_time'
        )
      )
      ->whereBetween('appointments.created_at', [$startDate, $endDate])
      ->whereNotNull('appointments.final_reason_id')
      ->groupBy('appointment_final_reasons.id', 'appointment_final_reasons.final_reason_name')
      ->orderByDesc('total_appointments')
      ->get();

    // Calculate SLA compliance percentage for each final reason
    foreach ($finalReasonMetrics as $metric) {
      $metric->sla_compliance_percentage =
        $metric->closed_appointments > 0
          ? round(($metric->closed_within_sla / $metric->closed_appointments) * 100, 2)
          : 0;
    }

    return view('appointment::reports.final_reason', compact('finalReasonMetrics', 'startDate', 'endDate'));
  }

  /**
   * Export appointments data to Excel.
   */
  public function exportExcel(Request $request)
  {
    $startDate = $request->get(
      'startDate',
      now()
        ->startOfMonth()
        ->format('Y-m-d')
    );
    $endDate = $request->get('endDate', now()->format('Y-m-d'));
    $reportType = $request->get('report_type', 'appointments');

    $fileName = 'appointment_' . $reportType . '_report_' . $startDate . '_to_' . $endDate . '.xlsx';

    return Excel::download(new AppointmentReportExport($startDate, $endDate, $reportType), $fileName);
  }
}
