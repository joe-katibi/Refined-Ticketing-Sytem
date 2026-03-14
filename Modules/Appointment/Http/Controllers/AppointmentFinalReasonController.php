<?php

namespace Modules\Appointment\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Appointment\Models\AppointmentFinalReason;

class AppointmentFinalReasonController extends Controller
{
    public function index()
    {
        $reasons = AppointmentFinalReason::with(['creator', 'editor'])->latest()->get();
        return view('appointment::Final_reason.index', compact('reasons'));
    }

    public function create()
    {
        return view('appointment::Final_reason.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'final_reason_name' => 'required|string|max:255',
            'final_reason_description' => 'nullable|string',
            'final_reason_status' => 'required|in:Active,Inactive'
        ]);

        $validated['created_by'] = auth()->id();

        AppointmentFinalReason::create($validated);

        return redirect()->route('appointment.final-reasons.index')
            ->with('success', 'Final reason created successfully.');
    }

    public function edit(AppointmentFinalReason $final_reason)
    {
        return view('appointment::Final_reason.edit', compact('final_reason'));
    }

    public function update(Request $request, AppointmentFinalReason $final_reason)
    {
        $validated = $request->validate([
            'final_reason_name' => 'required|string|max:255',
            'final_reason_description' => 'nullable|string',
            'final_reason_status' => 'required|in:Active,Inactive'
        ]);

        $validated['edited_by'] = auth()->id();

        $final_reason->update($validated);

        return redirect()->route('appointment.final-reasons.index')
            ->with('success', 'Final reason updated successfully.');
    }

    public function destroy(AppointmentFinalReason $final_reason)
    {
        $final_reason->delete();
        return redirect()->route('appointment.final-reasons.index')
            ->with('success', 'Final reason deleted successfully.');
    }
}
