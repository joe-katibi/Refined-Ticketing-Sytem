<?php

namespace App\Exports;

use Illuminate\Support\Collection;

class AuditResultsExport implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings
{
    protected $groupedResults;

    public function __construct(Collection $groupedResults)
    {
        $this->groupedResults = $groupedResults;
    }

    public function collection()
    {
        return $this->groupedResults->map(function ($group) {
            return [
                'Country' => $group['results']->first()->country_name ?? '',
                'Services' => $group['results']->first()->services == '1' ? 'Fiber' : 'DTH',
                'Week/Month' => $group['groupKey'] ?? '',
                'Average' => round($group['average'], 2) . '%',
            ];
        });
    }

    public function headings(): array
    {
        return ['Country', 'Services', 'Week/Month', 'Average'];
    }
}
