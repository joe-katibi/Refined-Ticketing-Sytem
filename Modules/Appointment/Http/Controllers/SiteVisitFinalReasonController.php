<?php

namespace Modules\Appointment\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Appointment\Models\SiteVisitFinalReason;

class SiteVisitFinalReasonController extends Controller
{
    /**
     * Display a listing of site visit final reasons
     */
    public function index()
    {
        $reasons = SiteVisitFinalReason::with(['creator', 'editor'])->latest()->get();
        return view('appointment::site_visit_final_reason.index', compact('reasons'));
    }

    /**
     * Show the form for creating a new site visit final reason
     */
    public function create()
    {
        return view('appointment::site_visit_final_reason.create');
    }

    /**
     * Store a newly created site visit final reason
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'final_reason_name' => 'required|string|max:255',
            'final_reason_description' => 'nullable|string',
            'final_reason_status' => 'required|in:Active,Inactive'
        ]);

        $validated['created_by'] = auth()->id();

        SiteVisitFinalReason::create($validated);

        return redirect()->route('site-visit.final-reasons.index')
            ->with('success', 'Site visit final reason created successfully.');
    }

    /**
     * Display the specified site visit final reason
     */
    public function show(SiteVisitFinalReason $final_reason)
    {
        return view('appointment::site_visit_final_reason.show', compact('final_reason'));
    }

    /**
     * Show the form for editing the specified site visit final reason
     */
    public function edit(SiteVisitFinalReason $final_reason)
    {
        return view('appointment::site_visit_final_reason.edit', compact('final_reason'));
    }

    /**
     * Update the specified site visit final reason
     */
    public function update(Request $request, SiteVisitFinalReason $final_reason)
    {
        $validated = $request->validate([
            'final_reason_name' => 'required|string|max:255',
            'final_reason_description' => 'nullable|string',
            'final_reason_status' => 'required|in:Active,Inactive'
        ]);

        $validated['edited_by'] = auth()->id();

        $final_reason->update($validated);

        return redirect()->route('site-visit.final-reasons.index')
            ->with('success', 'Site visit final reason updated successfully.');
    }

    /**
     * Remove the specified site visit final reason
     */
    public function destroy(SiteVisitFinalReason $final_reason)
    {
        $final_reason->delete();
        
        return redirect()->route('site-visit.final-reasons.index')
            ->with('success', 'Site visit final reason deleted successfully.');
    }
}
