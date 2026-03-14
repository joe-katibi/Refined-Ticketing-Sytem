<?php


namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ServiceExamReportExport implements FromArray, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        // Ensure the data is always an array
        $this->data = is_array($data) ? $data : $data->toArray();
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return ['Service', 'Country', 'Week', 'Month', 'Percentage', 'Grade'];
    }
}
