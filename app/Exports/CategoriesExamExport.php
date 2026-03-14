<?php

namespace App\Exports;

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CategoriesExamExport implements FromArray, WithHeadings
{
    protected $groupedResults;

    public function __construct($groupedResults)
    {
        $this->groupedResults = $groupedResults;
    }

    public function array(): array
    {
        $exportData = [];

        foreach ($this->groupedResults as $duration => $durationData) {
            foreach ($durationData['category_results'] as $categoryId => $categoryData) {
                foreach ($categoryData['category_results'] as $conductId => $result) {
                    $exportData[] = [
                        'Duration' => $duration,
                        'Category Name' => $result['category_name'],
                        'Country Name' => $result['country_name'],
                        'Course Name' => $result['course_name'],
                        'Service' => $result['s_id'] == 1 ? 'Fiber' : 'DTH',
                        'Correct Percentage' => $result['correct_percentage'] . '%',
                        'Grade' => $result['grade'],
                    ];
                }
            }
        }

        return $exportData;
    }

    public function headings(): array
    {
        return [
            'Duration',
            'Category Name',
            'Country Name',
            'Course Name',
            'Service',
            'Correct Percentage',
            'Grade',
        ];
    }
}
