<?php

namespace Modules\Outages\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use Modules\Outages\Exports\OltUploadTemplateExport;
use Modules\Outages\Imports\OltUploadImport;

class OltUploadController extends Controller
{
    public function create(): View
    {
        return view('outages::olt-upload.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new OltUploadImport();

        try {
            Excel::import($import, $request->file('file'));
        } catch (ValidationException $e) {
            return redirect()->route('olt-upload.create')
                ->with('error', 'The file could not be read: ' . $e->getMessage());
        }

        if ($import->processedRows === 0 && empty($import->errors)) {
            return redirect()->route('olt-upload.create')
                ->with('error', 'No rows were found in the uploaded file.');
        }

        return redirect()->route('olt-upload.create')
            ->with('success', "Processed {$import->processedRows} row(s) successfully.")
            ->with('import_errors', $import->errors);
    }

    public function template()
    {
        return Excel::download(new OltUploadTemplateExport(), 'olt-upload-template.xlsx');
    }
}
