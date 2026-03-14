<?php

namespace Modules\Outages\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Outages\Models\AffectedArea;

class AffectedAreaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $affected_areas = AffectedArea::with(['creator', 'editor'])->get();
        return view('outages::affected_area.index', compact('affected_areas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('outages::affected_area.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'area_name' => 'required|string|max:255',
            'area_description' => 'nullable|string',
            'area_status' => 'required|in:Active,Inactive'
        ]);

        $validated['created_by'] = auth()->id();

        AffectedArea::create($validated);

        return redirect()->route('outages.affected-areas.index')
            ->with('success', 'Affected area created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(AffectedArea $affectedArea)
    {
        return view('outages::affected_area.show', compact('affectedArea'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AffectedArea $affectedArea)
    {
        return view('outages::affected_area.edit', compact('affectedArea'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AffectedArea $affectedArea)
    {
        $validated = $request->validate([
            'area_name' => 'required|string|max:255',
            'area_description' => 'nullable|string',
            'area_status' => 'required|in:Active,Inactive'
        ]);

        $validated['edited_by'] = auth()->id();

        $affectedArea->update($validated);

        return redirect()->route('outages.affected-areas.index')
            ->with('success', 'Affected area updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AffectedArea $affectedArea)
    {
        $affectedArea->delete();

        return redirect()->route('outages.affected-areas.index')
            ->with('success', 'Affected area deleted successfully.');
    }
}
