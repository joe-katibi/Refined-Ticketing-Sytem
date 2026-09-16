<?php

namespace Modules\Appointment\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Blank template matching InstallationUploadImport's expected columns
 * exactly.
 */
class InstallationUploadTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [];
    }

    public function headings(): array
    {
        return [
            'Customer Name',
            'Account Number',
            'Contact Number',
            'Alternative Contact Number',
            'Date Received',
            'FDT',
            'OLT',
            'Road Name',
            'Dispatcher',
            'Team Assigned',
            'Installation Type',
            'Category',
            'Dispatch Update',
            'Escalation Date',
            'Location Coords',
            'Escalation Notes',
            'Infra Feedback',
            'Infra Feedback Date And Time',
            'Design Feedback',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
