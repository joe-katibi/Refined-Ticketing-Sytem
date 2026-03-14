<?php

namespace Modules\Outages\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Outages\Models\Fdt;
use Modules\Outages\Models\PonPort;
use Illuminate\Support\Facades\Auth;

class FdtController extends Controller
{
    /**
     * Display a listing of FDTs.
     */
    public function index(Request $request): View
    {
        $query = Fdt::with(['ponPort.oltSlot.olt', 'creator', 'editor', 'fats']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('fdt_number', 'like', "%{$search}%")
                  ->orWhere('fdt_type', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhereHas('ponPort.oltSlot.olt', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // FDT type filter
        if ($request->filled('fdt_type')) {
            $query->where('fdt_type', $request->get('fdt_type'));
        }

        $fdts = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get filter options
        $fdtTypes = ['8-port', '16-port', '24-port', '32-port'];
        $statuses = ['active', 'inactive', 'faulty', 'maintenance'];

        return view('outages::fdts.index', compact('fdts', 'fdtTypes', 'statuses'));
    }

    /**
     * Show the form for creating a new FDT.
     */
    public function create(PonPort $ponPort): View
    {
        $ponPort->load(['oltSlot.olt', 'fdts']);
        return view('outages::fdts.create', compact('ponPort'));
    }

    /**
     * Store a newly created FDT in storage.
     */
    public function store(Request $request, PonPort $ponPort): RedirectResponse
    {
        $validated = $request->validate([
            'fdt_number' => 'required|integer|min:1|unique:fdts,fdt_number,NULL,id,pon_port_id,' . $ponPort->id,
            'fdt_type' => 'nullable|string|in:8-port,16-port,24-port,32-port',
            'location' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1|max:32',
            'status' => 'required|in:active,inactive,faulty,maintenance'
        ]);

        $validated['pon_port_id'] = $ponPort->id;
        $validated['created_by'] = Auth::id();

        $fdt = Fdt::create($validated);

        return redirect()->route('olts.show', $ponPort->oltSlot->olt)
            ->with('success', 'FDT created successfully.');
    }

    /**
     * Display the specified FDT.
     */
    public function show(Fdt $fdt): View
    {
        $fdt->load([
            'ponPort.oltSlot.olt',
            'creator', 
            'editor', 
            'fats.creator',
            'fats.editor'
        ]);

        return view('outages::fdts.show', compact('fdt'));
    }

    /**
     * Show the form for editing the specified FDT.
     */
    public function edit(Fdt $fdt): View
    {
        $fdt->load(['ponPort.oltSlot.olt', 'fats']);
        
        return view('outages::fdts.edit', compact('fdt'));
    }

    /**
     * Update the specified FDT in storage.
     */
    public function update(Request $request, Fdt $fdt): RedirectResponse
    {
        $validated = $request->validate([
            'fdt_number' => 'required|integer|min:1|unique:fdts,fdt_number,' . $fdt->id . ',id,pon_port_id,' . $fdt->pon_port_id,
            'fdt_type' => 'nullable|string|in:8-port,16-port,24-port,32-port',
            'location' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1|max:32',
            'status' => 'required|in:active,inactive,faulty,maintenance'
        ]);

        $validated['edited_by'] = Auth::id();

        $fdt->update($validated);

        return redirect()->route('fdts.show', $fdt)
            ->with('success', 'FDT updated successfully.');
    }

    /**
     * Remove the specified FDT from storage.
     */
    public function destroy(Fdt $fdt): RedirectResponse
    {
        $olt = $fdt->ponPort->oltSlot->olt;
        
        // Check if FDT has FATs
        if ($fdt->fats()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete FDT that has FATs. Please delete all FATs first.');
        }

        $fdt->delete();

        return redirect()->route('olts.show', $olt)
            ->with('success', 'FDT deleted successfully.');
    }

    /**
     * Get FDTs for a specific PON port (AJAX endpoint).
     */
    public function getFdts(PonPort $ponPort)
    {
        $fdts = $ponPort->fdts()->active()->get(['id', 'fdt_number', 'fdt_type']);
        
        return response()->json($fdts);
    }
}
