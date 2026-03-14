<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductivityReportExport implements FromArray, WithHeadings
{
    protected $productivityResults;

    /**
     * Constructor to accept grouped results.
     */
    public function __construct(array $productivityResults)
    {
        $this->productivityResults = $productivityResults;
    }
    
    /**
     * Prepare data for export as an array.
     *
     * @return array
     */
    public function array(): array
{
    $exportData = [];

    foreach ($this->productivityResults as $record) {
        $exportData[] = [
            'Agent' => $record['agentName'] ?? 'N/A',
            'Category' => $record['category_name'] ?? 'N/A',
            'Services' => $record['service_name'] ?? 'N/A',
            'Country' => $record['country_name'] ?? 'N/A',
            'Date' => isset($record['created_at']) ? Carbon::parse($record['created_at'])->format('d-M-Y') : 'N/A',
            'Period' => $record['monthName'] ?? 'N/A', // Assuming the period is in `monthName`
            'Marks' => $record['final_results'] ?? 0,  // Adjusted to match `final_results`
            'Average Marks' => 0, // Placeholder if no grouped average is available
        ];
    }

    return $exportData;
}

    

    /**
     * Define the headings for the export file.
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
            'Date',
            'Period',
            'Marks',
            'Average Marks',
        ];
    }
}
