<?php

namespace Modules\Outages\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Outages\Models\AffectedService;

class AffectedServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $affected_services = AffectedService::with(['creator', 'editor'])->get();
        return view('outages::affected_service.index', compact('affected_services'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('outages::affected_service.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_name' => 'required|string|max:255',
            'service_description' => 'nullable|string',
            'service_status' => 'required|in:Active,Inactive'
        ]);

        $validated['created_by'] = auth()->id();

        AffectedService::create($validated);

        return redirect()->route('outages.affected-services.index')
            ->with('success', 'Affected service created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(AffectedService $affectedService)
    {
        return view('outages::affected_service.show', compact('affectedService'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AffectedService $affectedService)
    {
        return view('outages::affected_service.edit', compact('affectedService'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AffectedService $affectedService)
    {
        $validated = $request->validate([
            'service_name' => 'required|string|max:255',
            'service_description' => 'nullable|string',
            'service_status' => 'required|in:Active,Inactive'
        ]);

        $validated['edited_by'] = auth()->id();

        $affectedService->update($validated);

        return redirect()->route('outages.affected-services.index')
            ->with('success', 'Affected service updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AffectedService $affectedService)
    {
        $affectedService->delete();

        return redirect()->route('outages.affected-services.index')
            ->with('success', 'Affected service deleted successfully.');
    }
}
