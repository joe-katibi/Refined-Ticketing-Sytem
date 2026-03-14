<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ServiceReportExport implements FromCollection, WithHeadings
{
    protected $serviceReport;

    public function __construct($serviceReport)
    {
        $this->serviceReport = $serviceReport;
    }

    public function collection()
    {
        return collect($this->serviceReport->map(function ($item) {
            return [
                'ID' => $item->id,
                'Agent Name' => $item->agentName ?? '',
                'Supervisor Name' => $item->SupervisorName ?? '',
                'Quality Analyst Name' => $item->qualityName ?? '',
                'Customer Account' => $item->customer_account,
                'Recording ID' => $item->recording_id,
                'Final Results' => $item->final_results. '%',
                'Category Name' => $item->category_name,
                'Service Name' => $item->service_name,
                'Country Name' => $item->country_name,
                'Month' => $item->monthName ?? '',
                'Week' => $item->weekNumberWithPrefix ?? '',
            ];
        }));
    }

    public function headings(): array
    {
        return [
            'ID',
            'Agent Name',
            'Supervisor Name',
            'Quality Analyst Name',
            'Customer Account',
            'Recording ID',
            'Final Results',
            'Category Name',
            'Service Name',
            'Country Name',
            'Month',
            'Week',
        ];
    }
}
