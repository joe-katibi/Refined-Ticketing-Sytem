<?php

namespace Modules\Appointment\Exports;

use App\Traits\BucketsTicketAge;
use App\Traits\SanitizesExcelOutput;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AppointmentReportExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    use BucketsTicketAge;
    use SanitizesExcelOutput;

    protected $startDate;

    protected $endDate;

    protected $reportType;

    public function __construct($startDate, $endDate, $reportType = 'appointments')
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->reportType = $reportType;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        switch ($this->reportType) {
            case 'sla':
                return $this->getSlaData();
            case 'team_productivity':
                return $this->getTeamProductivityData();
            case 'sub_team_productivity':
                return $this->getSubTeamProductivityData();
            case 'assigned_team_productivity':
                return $this->getAssignedTeamProductivityData();
            case 'final_reason':
                return $this->getFinalReasonData();
            case 'region':
                return $this->getRegionData();
            case 'open_tickets':
                return $this->getOpenTicketsData();
            default:
                return $this->getAppointmentsData();
        }
    }

    public function headings(): array
    {
        switch ($this->reportType) {
            case 'sla':
                return [
                    'Date',
                    'Total Appointments',
                    'Closed Appointments',
                    'Within SLA (2hrs)',
                    'Outside SLA',
                    'SLA Compliance %',
                ];
            case 'team_productivity':
                return [
                    'Team Name',
                    'Total Assigned',
                    'Total Closed',
                    'Within SLA',
                    'Outside SLA',
                    'SLA Compliance %',
                    'Avg Resolution Time (hrs)',
                ];
            case 'sub_team_productivity':
                return [
                    'Sub Team Name',
                    'Total Assigned',
                    'Total Closed',
                    'Within SLA',
                    'Outside SLA',
                    'SLA Compliance %',
                    'Avg Resolution Time (hrs)',
                ];
            case 'assigned_team_productivity':
                return [
                    'Assigned User',
                    'Total Assigned',
                    'Total Closed',
                    'Within SLA',
                    'Outside SLA',
                    'SLA Compliance %',
                    'Avg Resolution Time (hrs)',
                ];
            case 'final_reason':
                return [
                    'Final Reason',
                    'Total Appointments',
                    'Closed Appointments',
                    'Open Appointments',
                    'Within SLA',
                    'SLA Compliance %',
                    'Avg Resolution Time (hrs)',
                ];
            case 'region':
                return [
                    'Region',
                    'Ticket ID',
                    'Account Number',
                    'Status',
                    'SLA Status',
                    'Time Since Raised',
                    'Feedback',
                    'Final Reason',
                    'Created Date',
                ];
            case 'open_tickets':
                return [
                    'Ticket ID',
                    'Account Number',
                    'Status',
                    'OLT',
                    'Sub Category',
                    'Team',
                    'Time Since Raised',
                    'Created Date',
                ];
            default:
                return [
                    'ID',
                    'Account Number',
                    'Ticket ID',
                    'Description',
                    'Appointment Type',
                    'Status',
                    'Priority',
                    'Team',
                    'Sub Team',
                    'Closed By',
                    'Final Reason',
                    'Created Date',
                    'Completed Date',
                    'Completed Time',
                    'Scheduled Date',
                    'Scheduled Time',
                    'OLT ID',
                ];
        }
    }

    public function map($row): array
    {
        switch ($this->reportType) {
            case 'sla':
                return [
                    $row->date,
                    $row->total,
                    $row->closed,
                    $row->within_sla,
                    $row->outside_sla,
                    $row->closed > 0 ? round(($row->within_sla / $row->closed) * 100, 2).'%' : '0%',
                ];
            case 'team_productivity':
            case 'sub_team_productivity':
            case 'assigned_team_productivity':
                return [
                    $this->sanitizeExcelValue($row->team_name ?? ($row->sub_type_name ?? $row->name)),
                    $row->total_assigned,
                    $row->total_closed,
                    $row->closed_within_sla,
                    $row->closed_outside_sla,
                    $row->sla_compliance_percentage.'%',
                    $row->avg_resolution_time ?? 'N/A',
                ];
            case 'final_reason':
                return [
                    $this->sanitizeExcelValue($row->final_reason_name),
                    $row->total_appointments,
                    $row->closed_appointments,
                    $row->open_appointments,
                    $row->closed_within_sla,
                    $row->sla_compliance_percentage.'%',
                    $row->avg_resolution_time ?? 'N/A',
                ];
            case 'region':
                return [
                    $this->sanitizeExcelValue($row->region_name ?? 'Unassigned'),
                    $row->appointment_ticket_id ?? 'N/A',
                    $this->sanitizeExcelValue($row->account_number ?? 'N/A'),
                    $row->status,
                    $row->sla_status,
                    $row->age_bucket,
                    $this->sanitizeExcelValue($row->feedback ?? 'N/A'),
                    $this->sanitizeExcelValue($row->final_reason_name ?? 'N/A'),
                    $row->created_at,
                ];
            case 'open_tickets':
                return [
                    $row->appointment_ticket_id ?? 'N/A',
                    $this->sanitizeExcelValue($row->account_number ?? 'N/A'),
                    $row->status,
                    $this->sanitizeExcelValue($row->olt_name),
                    $this->sanitizeExcelValue($row->sub_category_name),
                    $this->sanitizeExcelValue($row->team_name),
                    $row->age_bucket,
                    $row->created_at,
                ];
            default:
                $resolutionTime = null;
                $withinSla = 'N/A';

                if ($row->completed_date && in_array($row->status, ['Completed', 'Closed'])) {
                    $created = \Carbon\Carbon::parse($row->created_at);
                    $completed = \Carbon\Carbon::parse($row->completed_date);
                    $resolutionTime = $created->diffInHours($completed);
                    $withinSla = $resolutionTime <= 2 ? 'Yes' : 'No';
                }

                return [
                    $row->id,
                    $this->sanitizeExcelValue($row->account_number ?? 'N/A'),
                    $row->appointment_ticket_id ?? 'N/A',
                    $this->sanitizeExcelValue($row->description_notes ?? 'N/A'),
                    $this->sanitizeExcelValue($row->appointment_type_name ?? 'N/A'),
                    $row->status,
                    $row->priority ?? 'N/A',
                    $this->sanitizeExcelValue($row->team_name ?? 'N/A'),
                    $this->sanitizeExcelValue($row->sub_type_name ?? 'N/A'),
                    $this->sanitizeExcelValue($row->closed_by_user ?? 'N/A'),
                    $this->sanitizeExcelValue($row->final_reason_name ?? 'N/A'),
                    $row->created_at,
                    $row->completed_date ?? 'N/A',
                    $row->completed_time ?? 'N/A',
                    $row->scheduled_date ?? 'N/A',
                    $row->scheduled_time ?? 'N/A',
                    $row->olt_id ?? 'N/A',
                ];
        }
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function getSlaData()
    {
        return DB::table('appointments')
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
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();
    }

    private function getTeamProductivityData()
    {
        $data = DB::table('team_types')
            ->leftJoin('appointments', function ($join) {
                $join
                    ->on('appointments.assigned_team_id', '=', 'team_types.id')
                    ->whereBetween('appointments.created_at', [$this->startDate.' 00:00:00', $this->endDate.' 23:59:59']);
            })
            ->select(
                'team_types.type_name as team_name',
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

        foreach ($data as $item) {
            $item->sla_compliance_percentage =
              $item->total_closed > 0 ? round(($item->closed_within_sla / $item->total_closed) * 100, 2) : 0;
        }

        return $data;
    }

    private function getSubTeamProductivityData()
    {
        $data = DB::table('appointments')
            ->join('sub_team_types', 'appointments.sub_team_type_id', '=', 'sub_team_types.id')
            ->select(
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
            ->whereBetween('appointments.created_at', [$this->startDate, $this->endDate])
            ->whereNotNull('appointments.sub_team_type_id')
            ->groupBy('sub_team_types.id', 'sub_team_types.sub_type_name')
            ->orderByDesc('total_closed')
            ->get();

        foreach ($data as $item) {
            $item->sla_compliance_percentage =
              $item->total_closed > 0 ? round(($item->closed_within_sla / $item->total_closed) * 100, 2) : 0;
        }

        return $data;
    }

    private function getAssignedTeamProductivityData()
    {
        $data = DB::table('appointments')
            ->join('users', 'appointments.closed_by', '=', 'users.id')
            ->select(
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
            ->whereBetween('appointments.created_at', [$this->startDate, $this->endDate])
            ->whereNotNull('appointments.closed_by')
            ->whereIn('appointments.status', ['Completed', 'Closed', 'Scheduled-Closed'])
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_closed')
            ->get();

        foreach ($data as $item) {
            $item->sla_compliance_percentage =
              $item->total_closed > 0 ? round(($item->closed_within_sla / $item->total_closed) * 100, 2) : 0;
        }

        return $data;
    }

    private function getFinalReasonData()
    {
        $data = DB::table('appointments')
            ->join('appointment_final_reasons', 'appointments.final_reason_id', '=', 'appointment_final_reasons.id')
            ->select(
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
            ->whereBetween('appointments.created_at', [$this->startDate, $this->endDate])
            ->whereNotNull('appointments.final_reason_id')
            ->groupBy('appointment_final_reasons.id', 'appointment_final_reasons.final_reason_name')
            ->orderByDesc('total_appointments')
            ->get();

        foreach ($data as $item) {
            $item->sla_compliance_percentage =
              $item->closed_appointments > 0 ? round(($item->closed_within_sla / $item->closed_appointments) * 100, 2) : 0;
        }

        return $data;
    }

    /**
     * Row-level export for the region report — mirrors the SLA/feedback
     * computation in ReportsController::regionReport() exactly, since the
     * export needs the same per-appointment breakdown, not an aggregate.
     */
    private function getRegionData()
    {
        $closedStatuses = ['Completed', 'Closed', 'Scheduled-Closed'];

        return DB::table('appointments')
            ->leftJoin('regions', 'appointments.region_id', '=', 'regions.id')
            ->leftJoin('appointment_final_reasons', 'appointments.final_reason_id', '=', 'appointment_final_reasons.id')
            ->select(
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
            ->whereBetween('appointments.created_at', [$this->startDate, $this->endDate])
            ->orderBy('regions.name')
            ->orderByDesc('appointments.created_at')
            ->get()
            ->map(function ($row) use ($closedStatuses) {
                $isClosed = in_array($row->status, $closedStatuses);
                $created = \Carbon\Carbon::parse($row->created_at);

                if ($isClosed && $row->completed_date) {
                    $hours = $created->diffInHours(\Carbon\Carbon::parse($row->completed_date));
                    $row->sla_status = $hours <= 2 ? 'Within SLA' : 'Breached';
                } elseif ($isClosed) {
                    $hours = $created->diffInHours(now());
                    $row->sla_status = 'Unknown';
                } else {
                    $hours = $created->diffInHours(now());
                    $row->sla_status = $hours <= 2 ? 'Within SLA (pending)' : 'Overdue';
                }

                $row->age_bucket = $this->ageBucketLabel($hours);
                $row->feedback = $row->comment ?: ($row->dispatch_update ?: ($row->infra_feedback ?: $row->design_feedback));

                return $row;
            });
    }

    /**
     * Row-level export for the Open Tickets report — mirrors
     * ReportsController::openTicketsReport()'s $tickets query exactly.
     */
    private function getOpenTicketsData()
    {
        $closedStatuses = ['Completed', 'Closed', 'Scheduled-Closed'];

        return DB::table('appointments')
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
            ->whereBetween('appointments.created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('appointments.status', $closedStatuses)
            ->orderByDesc('appointments.created_at')
            ->get()
            ->map(function ($row) {
                $hours = \Carbon\Carbon::parse($row->created_at)->diffInHours(now());
                $row->age_bucket = $this->ageBucketLabel($hours);
                $row->olt_name = $row->olt_name ?? 'Unassigned';
                $row->sub_category_name = $row->sub_category_name ?? 'Uncategorized';
                $row->team_name = $row->team_name ?? 'Unassigned';

                return $row;
            });
    }

    private function getAppointmentsData()
    {
        return DB::table('appointments')
            ->leftJoin('appointment_types', 'appointments.appointment_type_id', '=', 'appointment_types.id')
            ->leftJoin('operational_teams', 'appointments.assigned_team_id', '=', 'operational_teams.id')
            ->leftJoin('sub_team_types', 'appointments.sub_team_type_id', '=', 'sub_team_types.id')
            ->leftJoin('users as closed_users', 'appointments.closed_by', '=', 'closed_users.id')
            ->leftJoin('appointment_final_reasons', 'appointments.final_reason_id', '=', 'appointment_final_reasons.id')
            ->select(
                'appointments.id',
                'appointments.account_number',
                'appointments.appointment_ticket_id',
                'appointments.description_notes',
                'appointment_types.type_name as appointment_type_name',
                'appointments.status',
                'appointments.priority',
                'operational_teams.team_name',
                'sub_team_types.sub_type_name',
                'closed_users.name as closed_by_user',
                'appointment_final_reasons.final_reason_name',
                'appointments.created_at',
                'appointments.completed_date',
                'appointments.completed_time',
                'appointments.scheduled_date',
                'appointments.scheduled_time',
                'appointments.olt_id'
            )
            ->whereBetween('appointments.created_at', [$this->startDate, $this->endDate])
            ->orderBy('appointments.created_at', 'desc')
            ->get();
    }
}
