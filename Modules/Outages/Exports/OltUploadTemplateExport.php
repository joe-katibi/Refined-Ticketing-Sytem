<?php

namespace Modules\Outages\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Blank template matching OltUploadImport's expected columns exactly.
 */
class OltUploadTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [];
    }

    public function headings(): array
    {
        return [
            'OLT Name',
            'Vendor',
            'IP address',
            'Location',
            'FDT',
            'FAT',
            'Slots',
            'PON Ports',
            'Customer Account Number',
            'Customer Name',
            'Onu Type',
            'ONU physical Address',
            'Bandwidth Profile Name',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
