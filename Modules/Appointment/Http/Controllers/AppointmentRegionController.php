<?php

namespace Modules\Appointment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\Request;
use Modules\Outages\Models\Olt;

class AppointmentRegionController extends Controller
{
    public function index()
    {
        $regions = Region::with(['creator', 'editor', 'olts'])->latest()->get();

        return view('appointment::regions.index', compact('regions'));
    }

    public function create()
    {
        $olts = Olt::orderBy('name')->get();

        return view('appointment::regions.create', compact('olts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:regions,name',
            'status' => 'required|in:Active,Inactive',
            'olts' => 'nullable|array',
            'olts.*' => 'exists:olts,id',
        ]);

        $region = Region::create([
            'name' => $validated['name'],
            'status' => $validated['status'],
            'created_by' => auth()->id(),
        ]);

        // olts.region_id is a plain belongsTo (an OLT sits in exactly one
        // region), so "assigning" the multi-select here means pointing the
        // selected OLTs' region_id at this new region — there's nothing to
        // unassign yet since the region didn't exist a moment ago.
        Olt::whereIn('id', $validated['olts'] ?? [])->update(['region_id' => $region->id]);

        return redirect()->route('appointment.regions.index')
            ->with('success', 'Region created successfully.');
    }

    public function edit(Region $region)
    {
        $region->load('olts');
        $olts = Olt::orderBy('name')->get();

        return view('appointment::regions.edit', compact('region', 'olts'));
    }

    public function update(Request $request, Region $region)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:regions,name,'.$region->id,
            'status' => 'required|in:Active,Inactive',
            'olts' => 'nullable|array',
            'olts.*' => 'exists:olts,id',
        ]);

        $region->update([
            'name' => $validated['name'],
            'status' => $validated['status'],
            'edited_by' => auth()->id(),
        ]);

        $selectedOltIds = $validated['olts'] ?? [];

        // Assign the newly-selected OLTs to this region, and release any
        // OLT that was in this region but got deselected — otherwise an
        // OLT removed from the multi-select would silently stay attached.
        Olt::whereIn('id', $selectedOltIds)->update(['region_id' => $region->id]);
        Olt::where('region_id', $region->id)
            ->whereNotIn('id', $selectedOltIds)
            ->update(['region_id' => null]);

        return redirect()->route('appointment.regions.index')
            ->with('success', 'Region updated successfully.');
    }
}
