<?php

namespace Modules\Escalations\Exports;

use App\Traits\SanitizesExcelOutput;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EscalationReportExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    use SanitizesExcelOutput;

    protected $dateFrom;

    protected $dateTo;

    protected $reportType;

    public function __construct($dateFrom, $dateTo, $reportType = 'escalations')
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
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
            case 'productivity':
                return $this->getProductivityData();
            case 'sub_category':
                return $this->getSubCategoryData();
            default:
                return $this->getEscalationsData();
        }
    }

    public function headings(): array
    {
        switch ($this->reportType) {
            case 'sla':
                return [
                    'Date',
                    'Total Escalations',
                    'Closed Escalations',
                    'Within SLA (4hrs)',
                    'Outside SLA',
                    'SLA Compliance %',
                ];
            case 'productivity':
                return [
                    'User Name',
                    'Total Closed',
                    'Within SLA',
                    'Outside SLA',
                    'SLA Compliance %',
                    'Avg Resolution Time (hrs)',
                ];
            case 'sub_category':
                return [
                    'Category',
                    'Sub Category',
                    'Total Escalations',
                    'Closed Escalations',
                    'Within SLA',
                    'SLA Compliance %',
                    'Avg Resolution Time (hrs)',
                ];
            default:
                return [
                    'ID',
                    'Escalation ID',
                    'Ticket ID',
                    'Account Number',
                    'Description',
                    'Category',
                    'Sub Category',
                    'Status',
                    'Priority',
                    'Sub Department',
                    'Closed By',
                    'Created Date',
                    'Closed Date',
                    'Closed Time',
                    'Resolution Time (hrs)',
                    'Within SLA',
                ];
        }
    }

    public function map($row): array
    {
        switch ($this->reportType) {
            case 'sla':
                return [
                    $row->date,
                    $row->total_escalations,
                    $row->closed_escalations,
                    $row->closed_within_sla,
                    $row->closed_outside_sla,
                    $row->sla_compliance_percentage.'%',
                ];
            case 'productivity':
                return [
                    $this->sanitizeExcelValue($row->name),
                    $row->total_closed,
                    $row->closed_within_sla,
                    $row->closed_outside_sla,
                    $row->sla_compliance_percentage.'%',
                    $row->avg_resolution_time ?? 'N/A',
                ];
            case 'sub_category':
                return [
                    $this->sanitizeExcelValue($row->category_name ?? 'N/A'),
                    $this->sanitizeExcelValue($row->subcategory_name ?? 'N/A'),
                    $row->total_escalations,
                    $row->closed_escalations,
                    $row->closed_within_sla,
                    $row->sla_compliance_percentage.'%',
                    $row->avg_resolution_time ?? 'N/A',
                ];
            default:
                $resolutionTime = null;
                $withinSla = 'N/A';
                $closedDate = 'N/A';
                $closedTime = 'N/A';

                if ($row->closed_at) {
                    $created = \Carbon\Carbon::parse($row->created_at);
                    $closed = \Carbon\Carbon::parse($row->closed_at);
                    $resolutionTime = $created->diffInHours($closed);
                    $withinSla = $resolutionTime <= 4 ? 'Yes' : 'No';
                    $closedDate = $closed->format('Y-m-d');
                    $closedTime = $closed->format('H:i:s');
                }

                return [
                    $row->id,
                    $row->escalation_id ?? 'N/A',
                    $row->ticket_id ?? 'N/A',
                    $this->sanitizeExcelValue($row->account_number ?? 'N/A'),
                    $this->sanitizeExcelValue($row->description ?? 'N/A'),
                    $this->sanitizeExcelValue($row->category_name ?? 'N/A'),
                    $this->sanitizeExcelValue($row->sub_category_name ?? 'N/A'),
                    $row->status,
                    $row->priority ?? 'N/A',
                    $this->sanitizeExcelValue($row->sub_department_name ?? 'N/A'),
                    $this->sanitizeExcelValue($row->closed_by_user ?? 'N/A'),
                    $row->created_at,
                    $closedDate,
                    $closedTime,
                    $resolutionTime ?? 'N/A',
                    $withinSla,
                ];
        }
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function getSlaData()
    {
        return DB::table('escalations')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total_escalations'),
                DB::raw('SUM(CASE WHEN escalations.status IN ("Scheduled-Closed", "Escalated-Closed") THEN 1 ELSE 0 END) as closed_escalations'),
                DB::raw('SUM(CASE WHEN escalations.status IN ("Scheduled-Closed", "Escalated-Closed") AND TIMESTAMPDIFF(HOUR, escalations.created_at, escalations.closed_at) <= 4 THEN 1 ELSE 0 END) as closed_within_sla'),
                DB::raw('SUM(CASE WHEN escalations.status IN ("Scheduled-Closed", "Escalated-Closed") AND TIMESTAMPDIFF(HOUR, escalations.created_at, escalations.closed_at) > 4 THEN 1 ELSE 0 END) as closed_outside_sla')
            )
            ->whereBetween('created_at', [$this->dateFrom, $this->dateTo])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                $item->sla_compliance_percentage = $item->closed_escalations > 0
                    ? round(($item->closed_within_sla / $item->closed_escalations) * 100, 2)
                    : 0;

                return $item;
            });
    }

    private function getProductivityData()
    {
        $data = DB::table('escalations')
            ->join('users', 'escalations.created_by', '=', 'users.id')
            ->select(
                'users.name',
                DB::raw('COUNT(*) as total_closed'),
                DB::raw('SUM(CASE WHEN TIMESTAMPDIFF(HOUR, escalations.created_at, escalations.closed_at) <= 4 THEN 1 ELSE 0 END) as closed_within_sla'),
                DB::raw('SUM(CASE WHEN TIMESTAMPDIFF(HOUR, escalations.created_at, escalations.closed_at) > 4 THEN 1 ELSE 0 END) as closed_outside_sla'),
                DB::raw('ROUND(AVG(TIMESTAMPDIFF(HOUR, escalations.created_at, escalations.closed_at)), 2) as avg_resolution_time')
            )
            ->whereBetween('escalations.created_at', [$this->dateFrom, $this->dateTo])
            ->whereIn('escalations.status', ['Scheduled-Closed', 'Escalated-Closed'])
            ->whereNotNull('escalations.created_by')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_closed')
            ->get();

        foreach ($data as $item) {
            $item->sla_compliance_percentage = $item->total_closed > 0
                ? round(($item->closed_within_sla / $item->total_closed) * 100, 2)
                : 0;
        }

        return $data;
    }

    private function getSubCategoryData()
    {
        $data = DB::table('escalations')
            ->leftJoin('categories', 'escalations.category_id', '=', 'categories.id')
            ->leftJoin('subcategories', 'escalations.sub_category_id', '=', 'subcategories.id')
            ->select(
                'categories.category_name',
                'subcategories.sub_category_name as subcategory_name',
                DB::raw('COUNT(*) as total_escalations'),
                DB::raw('SUM(CASE WHEN escalations.status IN ("Scheduled-Closed", "Escalated-Closed") THEN 1 ELSE 0 END) as closed_escalations'),
                DB::raw('SUM(CASE WHEN escalations.status IN ("Scheduled-Closed", "Escalated-Closed") AND TIMESTAMPDIFF(HOUR, escalations.created_at, escalations.closed_at) <= 4 THEN 1 ELSE 0 END) as closed_within_sla'),
                DB::raw('ROUND(AVG(CASE WHEN escalations.status IN ("Scheduled-Closed", "Escalated-Closed") THEN TIMESTAMPDIFF(HOUR, escalations.created_at, escalations.closed_at) END), 2) as avg_resolution_time')
            )
            ->whereBetween('escalations.created_at', [$this->dateFrom, $this->dateTo])
            ->groupBy('categories.id', 'categories.category_name', 'subcategories.id', 'subcategories.sub_category_name')
            ->orderByDesc('total_escalations')
            ->get();

        foreach ($data as $item) {
            $item->sla_compliance_percentage = $item->closed_escalations > 0
                ? round(($item->closed_within_sla / $item->closed_escalations) * 100, 2)
                : 0;
        }

        return $data;
    }

    private function getEscalationsData()
    {
        return DB::table('escalations')
            ->leftJoin('categories', 'escalations.category_id', '=', 'categories.id')
            ->leftJoin('subcategories', 'escalations.sub_category_id', '=', 'subcategories.id')
            ->leftJoin('sub_departments', 'escalations.sub_department_id', '=', 'sub_departments.id')
            ->leftJoin('users as closed_users', 'escalations.created_by', '=', 'closed_users.id')
            ->select(
                'escalations.id',
                'escalations.escalation_id',
                'escalations.ticket_id',
                'escalations.account_number',
                'escalations.description',
                'categories.category_name',
                'subcategories.sub_category_name',
                'escalations.status',
                'escalations.priority',
                'sub_departments.sub_department_name',
                'closed_users.name as closed_by_user', // Using created_by instead of closed_by
                'escalations.created_at',
                'escalations.closed_at'
            )
            ->whereBetween('escalations.created_at', [$this->dateFrom, $this->dateTo])
            ->orderBy('escalations.created_at', 'desc')
            ->get();
    }
}
