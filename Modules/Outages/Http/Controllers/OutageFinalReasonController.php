<?php

namespace Modules\Outages\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Outages\Models\OutageFinalReason;

class OutageFinalReasonController extends Controller
{
    public function index()
    {
        $reasons = OutageFinalReason::with(['creator', 'editor'])->latest()->get();
        return view('outages::final_reason.index', compact('reasons'));
    }

    public function create()
    {
        return view('outages::final_reason.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'final_reason_name' => 'required|string|max:255',
            'final_reason_description' => 'nullable|string',
            'final_reason_status' => 'required|in:Active,Inactive'
        ]);

        $validated['created_by'] = auth()->id();

        OutageFinalReason::create($validated);

        return redirect()->route('outages.final-reasons.index')
            ->with('success', 'Final reason created successfully.');
    }

    public function edit(OutageFinalReason $final_reason)
    {
        return view('outages::final_reason.edit', compact('final_reason'));
    }

    public function update(Request $request, OutageFinalReason $final_reason)
    {
        $validated = $request->validate([
            'final_reason_name' => 'required|string|max:255',
            'final_reason_description' => 'nullable|string',
            'final_reason_status' => 'required|in:Active,Inactive'
        ]);

        $validated['edited_by'] = auth()->id();

        $final_reason->update($validated);

        return redirect()->route('outages.final-reasons.index')
            ->with('success', 'Final reason updated successfully.');
    }

    public function destroy(OutageFinalReason $final_reason)
    {
        $final_reason->delete();
        return redirect()->route('outages.final-reasons.index')
            ->with('success', 'Final reason deleted successfully.');
    }
}
