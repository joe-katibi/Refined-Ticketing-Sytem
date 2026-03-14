<?php

namespace App\Exports;

use App\Models\CoachingFrom;
use Maatwebsite\Excel\Concerns\FromCollection;

class CoachingReportExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return CoachingFrom::all();
    }
}
