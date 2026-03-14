<?php

namespace Modules\Outages\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Outages\Models\Fat;
use Modules\Outages\Models\Fdt;
use Illuminate\Support\Facades\Auth;

class FatController extends Controller
{
    /**
     * Display a listing of FATs.
     */
    public function index(Request $request): View
    {
        $query = Fat::with(['fdt.ponPort.oltSlot.olt', 'creator', 'editor']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('fat_number', 'like', "%{$search}%")
                  ->orWhere('fat_type', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhereHas('fdt.ponPort.oltSlot.olt', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // FAT type filter
        if ($request->filled('fat_type')) {
            $query->where('fat_type', $request->get('fat_type'));
        }

        $fats = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get filter options
        $fatTypes = ['4-port', '8-port', '12-port', '16-port'];
        $statuses = ['active', 'inactive', 'faulty', 'maintenance'];

        return view('outages::fats.index', compact('fats', 'fatTypes', 'statuses'));
    }

    /**
     * Show the form for creating a new FAT.
     */
    public function create(Fdt $fdt): View
    {
        $fdt->load(['ponPort.oltSlot.olt', 'fats']);
        return view('outages::fats.create', compact('fdt'));
    }

    /**
     * Store a newly created FAT in storage.
     */
    public function store(Request $request, Fdt $fdt): RedirectResponse
    {
        $validated = $request->validate([
            'fat_number' => 'required|integer|min:1|unique:fats,fat_number,NULL,id,fdt_id,' . $fdt->id,
            'fat_type' => 'nullable|string|in:4-port,8-port,12-port,16-port',
            'location' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1|max:16',
            'status' => 'required|in:active,inactive,faulty,maintenance'
        ]);

        $validated['fdt_id'] = $fdt->id;
        $validated['created_by'] = Auth::id();

        $fat = Fat::create($validated);

        return redirect()->route('fdts.show', $fdt)
            ->with('success', 'FAT created successfully.');
    }

    /**
     * Display the specified FAT.
     */
    public function show(Fat $fat): View
    {
        $fat->load([
            'fdt.ponPort.oltSlot.olt',
            'creator', 
            'editor'
        ]);

        return view('outages::fats.show', compact('fat'));
    }

    /**
     * Show the form for editing the specified FAT.
     */
    public function edit(Fat $fat): View
    {
        $fat->load(['fdt.ponPort.oltSlot.olt']);
        
        return view('outages::fats.edit', compact('fat'));
    }

    /**
     * Update the specified FAT in storage.
     */
    public function update(Request $request, Fat $fat): RedirectResponse
    {
        $validated = $request->validate([
            'fat_number' => 'required|integer|min:1|unique:fats,fat_number,' . $fat->id . ',id,fdt_id,' . $fat->fdt_id,
            'fat_type' => 'nullable|string|in:4-port,8-port,12-port,16-port',
            'location' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1|max:16',
            'status' => 'required|in:active,inactive,faulty,maintenance'
        ]);

        $validated['edited_by'] = Auth::id();

        $fat->update($validated);

        return redirect()->route('fats.show', $fat)
            ->with('success', 'FAT updated successfully.');
    }

    /**
     * Remove the specified FAT from storage.
     */
    public function destroy(Fat $fat): RedirectResponse
    {
        $fdt = $fat->fdt;
        
        $fat->delete();

        return redirect()->route('fdts.show', $fdt)
            ->with('success', 'FAT deleted successfully.');
    }

    /**
     * Get FATs for a specific FDT (AJAX endpoint).
     */
    public function getFats(Fdt $fdt)
    {
        $fats = $fdt->fats()->active()->get(['id', 'fat_number', 'fat_type']);
        
        return response()->json($fats);
    }
}
