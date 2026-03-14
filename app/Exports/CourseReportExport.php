<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;

class CourseReportExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $groupedResults;

    // Constructor to accept data
    public function __construct($groupedResults)
    {
        $this->groupedResults = $groupedResults;
    }

    /**
     * Returns the collection of data to be exported.
     */
    public function collection()
    {
        $exportData = collect();

        foreach ($this->groupedResults as $key => $group) {
            foreach ($group['conduct_results'] as $conduct) {
                $exportData->push([
                    'Period' => $key,
                    'Course Name' => $conduct['course_name'] ?? 'N/A',
                    'Country Name' => $conduct['country_name'] ?? 'N/A',
                    'Service Type' => $conduct['s_id'] == 1 ? 'Fiber' : 'DTH',
                    'Correct Answers' => $conduct['correct_count'] ?? 0,
                    'Wrong Answers' => $conduct['wrong_count'] ?? 0,
                    'Total Marks Achieved' => $conduct['total_correct_marks'] ?? 0,
                    'Total Marks' => $conduct['total_marks'] ?? 0,
                    'Correct Percentage' => $conduct['correct_percentage'] ?? 0,
                ]);
            }
        }

        return $exportData;
    }

    /**
     * Returns the headers for the exported file.
     */
    public function headings(): array
    {
        return [
            'Period',
            'Course Name',
            'Country Name',
            'Service Type',
            'Correct Answers',
            'Wrong Answers',
            'Total Marks Achieved',
            'Total Marks',
            'Correct Percentage',
        ];
    }
}
