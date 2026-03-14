<?php

namespace App\Exports;

use App\Models\AlertForm;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AutoFailReportExport implements FromArray, WithHeadings
{
    protected $groupedResults;

    public function __construct($groupedResults)
    {
        $this->groupedResults = $groupedResults;
    }

    /**
     * Prepare data for export as an array
     *
     * @return array
     */
    public function array(): array
    {
        $exportData = [];

        foreach ($this->groupedResults as $month => $records) {
            foreach ($records as $record) {
                $exportData[] = [
                    'Agent' => $record['name'],
                    'Category' => $record['category_name'] ?? 'N/A',
                    'Services' => $record['service_name'],
                    'Country' => $record['country_name'],
                    'Customer Code' => $record['customer_account'],
                    'Date' => Carbon::parse($record['created_at'])->format('d-M-Y'),
                    'Week' => 'Week ' . Carbon::parse($record['created_at'])->weekOfYear,
                    'Month' => Carbon::parse($record['created_at'])->format('F Y'),
                    'Auto Fail Status' => $this->getAutoFailStatus($record['auto_status']),
                ];
            }
        }

        return $exportData;
    }

    /**
     * Define the headings for the export file
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Agent',
            'Category',
            'Services',
            'Country',
            'Customer Code',
            'Date',
            'Week',
            'Month',
            'Auto Fail Status',
        ];
    }

    /**
     * Get the status label based on the auto_status
     *
     * @param int $status
     * @return string
     */
    private function getAutoFailStatus($status): string
    {
        return match ($status) {
            3 => 'Completed',
            2 => 'Pending',
            1 => 'Slipping',
            default => 'Unknown',
        };
    }
}
