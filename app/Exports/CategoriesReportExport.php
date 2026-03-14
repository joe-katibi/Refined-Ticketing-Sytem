<?php


namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CategoriesReportExport implements FromCollection, WithHeadings
{
    protected $groupedResults;

    /**
     * Constructor to pass grouped results to the export class.
     *
     * @param Collection $groupedResults
     */
    public function __construct(Collection $groupedResults)
    {
        $this->groupedResults = $groupedResults;
    }

    /**
     * Map grouped results into a collection for export.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->groupedResults->flatMap(function ($group, $key) {
            return $group['category_results']->map(function ($categoryResult) use ($key) {
                return [
                    'Category' => $categoryResult['category_name'] ?? '',
                    'Country' => $categoryResult['country_name'] ?? 'N/A',
                    'Services' => ($categoryResult['s_id'] ?? 0) == 1 ? 'Fiber' : 'DTH',
                    'Week/Month' => $key,
                    'Percentage' => round($categoryResult['average'], 2) . '%',
                ];
            });
        });
    }

    /**
     * Define column headings for the export.
     *
     * @return array
     */
    public function headings(): array
    {
        return ['Category', 'Country', 'Services', 'Week/Month', 'Percentage'];
    }
}
