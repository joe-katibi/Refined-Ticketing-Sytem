<?php

namespace Modules\Appointment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use Modules\Appointment\Exports\InstallationUploadTemplateExport;
use Modules\Appointment\Imports\InstallationUploadImport;

class InstallationUploadController extends Controller
{
    public function create(): View
    {
        return view('appointment::installation-upload.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new InstallationUploadImport;

        try {
            Excel::import($import, $request->file('file'));
        } catch (ValidationException $e) {
            return redirect()->route('installation-upload.create')
                ->with('error', 'The file could not be read: '.$e->getMessage());
        }

        if ($import->processedRows === 0 && empty($import->errors)) {
            return redirect()->route('installation-upload.create')
                ->with('error', 'No rows were found in the uploaded file.');
        }

        return redirect()->route('installation-upload.create')
            ->with('success', "Processed {$import->processedRows} row(s) successfully.")
            ->with('import_errors', $import->errors);
    }

    public function template()
    {
        return Excel::download(new InstallationUploadTemplateExport, 'installation-upload-template.xlsx');
    }
}
