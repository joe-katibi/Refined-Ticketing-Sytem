<?php

namespace Modules\Appointment\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use App\Traits\BucketsTicketAge;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Appointment\Exports\AppointmentReportExport;
use Modules\Appointment\Models\Appointment;

class ReportsController extends Controller
{
    use BucketsTicketAge;

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
        // Normalized to end-of-day so a same-day record (created after midnight
        // on endDate) isn't silently excluded by whereBetween below â€” a bare
        // 'Y-m-d' string compares as midnight, not end of day (see the
        // $endDateTime = $endDate . ' 23:59:59' pattern already used elsewhere
        // in this file, which this now matches).
        $endDate = Carbon::parse($request->get('endDate', now()->format('Y-m-d')))->endOfDay()->format('Y-m-d H:i:s');

        // Overall SLA metrics (2 hours SLA for appointments)
        $totalAppointments = Appointment::whereBetween('created_at', [$startDate, $endDate])->count();

        $closedAppointments = Appointment::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['Scheduled-Closed', 'Completed', 'Closed'])
            ->count();

        $withinSla = Appointment::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['Scheduled-Closed', 'Completed', 'Closed'])
            ->whereRaw('TIMESTAMPDIFF(HOUR, created_at, completed_date) <= 2')
            ->count();

        $outsideSla = Appointment::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['Scheduled-Closed', 'Completed', 'Closed'])
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
        $startDateTime = $startDate.' 00:00:00';
        $endDateTime = $endDate.' 23:59:59';

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
        $startDateTime = $startDate.' 00:00:00';
        $endDateTime = $endDate.' 23:59:59';

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
        $endDate = Carbon::parse($request->get('endDate', now()->format('Y-m-d')))->endOfDay()->format('Y-m-d H:i:s');

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
        $endDate = Carbon::parse($request->get('endDate', now()->format('Y-m-d')))->endOfDay()->format('Y-m-d H:i:s');

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
     * Display the Region report: SLA status, feedback (why an appointment is
     * still pending / not yet closed), and final reason, grouped by region.
     *
     * Unlike the other report methods above, "SLA" here can't only be
     * computed for closed appointments (TIMESTAMPDIFF against completed_date)
     * — a region report needs to say something about appointments that are
     * STILL pending too. There's no existing "overdue"/"breached" concept
     * for Appointment (Escalations/Outages have their own sla_deadline
     * columns; Appointment doesn't), so pending appointments get a live
     * TIMESTAMPDIFF against NOW() against the same 2-hour threshold used
     * everywhere else in this controller.
     */
    public function regionReport(Request $request)
    {
        $startDate = $request->get(
            'startDate',
            now()
                ->startOfMonth()
                ->format('Y-m-d')
        );
        $endDate = Carbon::parse($request->get('endDate', now()->format('Y-m-d')))->endOfDay()->format('Y-m-d H:i:s');

        $closedStatuses = ['Completed', 'Closed', 'Scheduled-Closed'];

        // Region-level summary, same aggregate shape as the other reports.
        $regionMetrics = DB::table('appointments')
            ->join('regions', 'appointments.region_id', '=', 'regions.id')
            ->select(
                'regions.id',
                'regions.name',
                DB::raw('COUNT(*) as total_appointments'),
                DB::raw(
                    "SUM(CASE WHEN appointments.status IN ('Completed', 'Closed', 'Scheduled-Closed') THEN 1 ELSE 0 END) as closed_appointments"
                ),
                DB::raw(
                    "SUM(CASE WHEN appointments.status NOT IN ('Completed', 'Closed', 'Scheduled-Closed') THEN 1 ELSE 0 END) as open_appointments"
                ),
                DB::raw(
                    "SUM(CASE WHEN appointments.status IN ('Completed', 'Closed', 'Scheduled-Closed') AND TIMESTAMPDIFF(HOUR, appointments.created_at, appointments.completed_date) <= 2 THEN 1 ELSE 0 END) as closed_within_sla"
                )
            )
            ->whereBetween('appointments.created_at', [$startDate, $endDate])
            ->groupBy('regions.id', 'regions.name')
            ->orderByDesc('total_appointments')
            ->get();

        foreach ($regionMetrics as $metric) {
            $metric->sla_compliance_percentage =
              $metric->closed_appointments > 0
                ? round(($metric->closed_within_sla / $metric->closed_appointments) * 100, 2)
                : 0;
        }

        // Per-appointment detail rows: an aggregate can't show "why is THIS one
        // still pending", so the region-level summary above is paired with a
        // full row-level breakdown carrying SLA status, feedback, and final
        // reason per appointment.
        $appointments = DB::table('appointments')
            ->leftJoin('regions', 'appointments.region_id', '=', 'regions.id')
            ->leftJoin('appointment_final_reasons', 'appointments.final_reason_id', '=', 'appointment_final_reasons.id')
            ->select(
                'appointments.id',
                'appointments.appointment_ticket_id',
                'appointments.account_number',
                'appointments.status',
                'appointments.comment',
                'appointments.dispatch_update',
                'appointments.infra_feedback',
                'appointments.design_feedback',
                'appointments.created_at',
                'appointments.completed_date',
                'regions.name as region_name',
                'appointment_final_reasons.final_reason_name'
            )
            ->whereBetween('appointments.created_at', [$startDate, $endDate])
            ->orderBy('regions.name')
            ->orderByDesc('appointments.created_at')
            ->get()
            ->map(function ($row) use ($closedStatuses) {
                $isClosed = in_array($row->status, $closedStatuses);
                $created = Carbon::parse($row->created_at);

                if ($isClosed && $row->completed_date) {
                    $hours = $created->diffInHours(Carbon::parse($row->completed_date));
                    $row->sla_status = $hours <= 2 ? 'Within SLA' : 'Breached';
                    $row->sla_class = $hours <= 2 ? 'success' : 'danger';
                } elseif ($isClosed) {
                    // Closed but never had a completed_date recorded — can't
                    // compute a duration, so say so rather than guessing.
                    $hours = $created->diffInHours(now());
                    $row->sla_status = 'Unknown';
                    $row->sla_class = 'secondary';
                } else {
                    $hours = $created->diffInHours(now());
                    $row->sla_status = $hours <= 2 ? 'Within SLA (pending)' : 'Overdue';
                    $row->sla_class = $hours <= 2 ? 'info' : 'warning';
                }

                // "Time taken since raised" — how long the appointment has
                // existed, up to when it closed (or up to now, if still
                // open). Same $hours value already computed for SLA above,
                // just bucketed into wider bands for a quick-scan column.
                $row->age_hours = $hours;
                $row->age_bucket = $this->ageBucketLabel($hours);

                // comment is the one free-text field staff actually use while a
                // ticket is still open (see AppointmentController's edit_assigned
                // form); dispatch_update/infra_feedback/design_feedback only exist
                // on rows created via the installation bulk-upload importer, so
                // they're the fallback for that older data, not the primary source.
                $row->feedback = $row->comment ?: ($row->dispatch_update ?: ($row->infra_feedback ?: $row->design_feedback));
                $row->region_name = $row->region_name ?? 'Unassigned';

                return $row;
            });

        $regionGroups = $appointments->groupBy('region_name');

        return view('appointment::reports.region', compact('regionMetrics', 'regionGroups', 'startDate', 'endDate'));
    }

    /**
     * Display the Open Tickets report: still-open appointments broken down
     * three ways — by OLT, by sub category, and by team — plus a combined
     * per-ticket detail list. "Open" here uses the same closed-status list
     * as every other report in this controller (Completed/Closed/
     * Scheduled-Closed are closed; everything else counts as open).
     */
    public function openTicketsReport(Request $request)
    {
        $startDate = $request->get(
            'startDate',
            now()
                ->startOfMonth()
                ->format('Y-m-d')
        );
        $endDate = Carbon::parse($request->get('endDate', now()->format('Y-m-d')))->endOfDay()->format('Y-m-d H:i:s');

        $closedStatuses = ['Completed', 'Closed', 'Scheduled-Closed'];

        $baseOpenQuery = fn () => DB::table('appointments')
            ->whereBetween('appointments.created_at', [$startDate, $endDate])
            ->whereNotIn('appointments.status', $closedStatuses);

        $byOlt = $baseOpenQuery()
            ->join('olts', 'appointments.olt_id', '=', 'olts.id')
            ->select('olts.id', 'olts.name', DB::raw('COUNT(*) as open_count'))
            ->groupBy('olts.id', 'olts.name')
            ->orderByDesc('open_count')
            ->get();

        $bySubCategory = $baseOpenQuery()
            ->join('subcategories', 'appointments.sub_category_id', '=', 'subcategories.id')
            ->select('subcategories.id', 'subcategories.sub_category_name', DB::raw('COUNT(*) as open_count'))
            ->groupBy('subcategories.id', 'subcategories.sub_category_name')
            ->orderByDesc('open_count')
            ->get();

        $byTeam = $baseOpenQuery()
            ->join('operational_teams', 'appointments.assigned_team_id', '=', 'operational_teams.id')
            ->select('operational_teams.id', 'operational_teams.team_name', DB::raw('COUNT(*) as open_count'))
            ->groupBy('operational_teams.id', 'operational_teams.team_name')
            ->orderByDesc('open_count')
            ->get();

        $totalOpen = $baseOpenQuery()->count();

        // Combined per-ticket detail: every open ticket with all three
        // dimensions visible on one row, since a summary count can't show
        // WHICH tickets make up each bucket.
        $tickets = $baseOpenQuery()
            ->leftJoin('olts', 'appointments.olt_id', '=', 'olts.id')
            ->leftJoin('subcategories', 'appointments.sub_category_id', '=', 'subcategories.id')
            ->leftJoin('operational_teams', 'appointments.assigned_team_id', '=', 'operational_teams.id')
            ->select(
                'appointments.appointment_ticket_id',
                'appointments.account_number',
                'appointments.status',
                'appointments.created_at',
                'olts.name as olt_name',
                'subcategories.sub_category_name',
                'operational_teams.team_name'
            )
            ->orderByDesc('appointments.created_at')
            ->get()
            ->map(function ($row) {
                $hours = Carbon::parse($row->created_at)->diffInHours(now());
                $row->age_bucket = $this->ageBucketLabel($hours);
                $row->olt_name = $row->olt_name ?? 'Unassigned';
                $row->sub_category_name = $row->sub_category_name ?? 'Uncategorized';
                $row->team_name = $row->team_name ?? 'Unassigned';

                return $row;
            });

        return view(
            'appointment::reports.open_tickets',
            compact('byOlt', 'bySubCategory', 'byTeam', 'totalOpen', 'tickets', 'startDate', 'endDate')
        );
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

        $fileName = 'appointment_'.$reportType.'_report_'.$startDate.'_to_'.$endDate.'.xlsx';

        return Excel::download(new AppointmentReportExport($startDate, $endDate, $reportType), $fileName);
    }
}
