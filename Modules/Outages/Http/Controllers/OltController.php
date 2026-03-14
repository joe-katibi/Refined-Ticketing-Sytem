<?php

namespace Modules\Outages\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Outages\Models\Olt;
use Modules\Outages\Models\OltSlot;
use Modules\Outages\Models\PonPort;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OltController extends Controller
{
    /**
     * Display a listing of OLTs.
     */
    public function index(Request $request): View
    {
        $query = Olt::with(['creator', 'editor', 'slots']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('vendor', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Vendor filter
        if ($request->filled('vendor')) {
            $query->where('vendor', $request->get('vendor'));
        }

        $olts = $query->orderBy('name')->paginate(15);

        // Get filter options
        $vendors = Olt::distinct()->pluck('vendor')->filter()->sort();
        $statuses = ['Active', 'Inactive', 'Maintenance', 'Faulty'];

        return view('outages::olts.index', compact('olts', 'vendors', 'statuses'));
    }

    /**
     * Show the form for creating a new OLT.
     */
    public function create(): View
    {
        return view('outages::olts.create');
    }

    /**
     * Store a newly created OLT in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:olts,name',
            'vendor' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'ip_address' => 'required|ip|unique:olts,ip_address',
            'location' => 'nullable|string|max:255',
            'total_slots' => 'nullable|integer|min:1|max:32',
            'software_version' => 'nullable|string|max:255',
            'status' => 'required|in:Active,Inactive,Maintenance,Faulty'
        ]);

        $validated['created_by'] = Auth::id();

        $olt = Olt::create($validated);

        return redirect()->route('olts.show', $olt)
            ->with('success', 'OLT created successfully.');
    }

    /**
     * Display the specified OLT.
     */
    public function show(Olt $olt): View
    {
        $olt->load([
            'creator', 
            'editor', 
            'slots.ponPorts', 
            'slots.creator',
            'slots.editor'
        ]);

        return view('outages::olts.show', compact('olt'));
    }

    /**
     * Show the form for editing the specified OLT.
     */
    public function edit(Olt $olt): View
    {
        $olt->load(['slots.ponPorts']);
        
        return view('outages::olts.edit', compact('olt'));
    }

    /**
     * Update the specified OLT in storage.
     */
    public function update(Request $request, Olt $olt): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:olts,name,' . $olt->id,
            'vendor' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'ip_address' => 'required|ip|unique:olts,ip_address,' . $olt->id,
            'location' => 'nullable|string|max:255',
            'total_slots' => 'nullable|integer|min:1|max:32',
            'software_version' => 'nullable|string|max:255',
            'status' => 'required|in:Active,Inactive,Maintenance,Faulty'
        ]);

        $validated['edited_by'] = Auth::id();

        $olt->update($validated);

        return redirect()->route('olts.show', $olt)
            ->with('success', 'OLT updated successfully.');
    }

    /**
     * Remove the specified OLT from storage.
     */
    public function destroy(Olt $olt): RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            // Check if OLT has any slots or ports
            $slotsCount = $olt->slots()->count();
            $portsCount = $olt->ponPorts()->count();
            
            if ($slotsCount > 0 || $portsCount > 0) {
                return redirect()->route('olts.index')
                    ->with('error', "Cannot delete OLT. It has {$slotsCount} slots and {$portsCount} PON ports. Remove them first.");
            }

            $olt->delete();
            
            DB::commit();
            
            return redirect()->route('olts.index')
                ->with('success', 'OLT deleted successfully.');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->route('olts.index')
                ->with('error', 'Error deleting OLT: ' . $e->getMessage());
        }
    }

    /**
     * Create a new slot for the specified OLT.
     */
    public function createSlot(Olt $olt): View
    {
        return view('outages::olts.create-slot', compact('olt'));
    }

    /**
     * Store a new slot for the specified OLT.
     */
    public function storeSlot(Request $request, Olt $olt): RedirectResponse
    {
        $validated = $request->validate([
            'slot_number' => 'required|integer|min:0|max:31|unique:olt_slots,slot_number,NULL,id,olt_id,' . $olt->id,
            'slot_type' => 'nullable|string|in:GPON,XGSPON,EPON,10G-EPON',
            'status' => 'required|in:active,inactive,faulty,spare'
        ]);

        $validated['olt_id'] = $olt->id;
        $validated['created_by'] = Auth::id();

        OltSlot::create($validated);

        return redirect()->route('olts.show', $olt)
            ->with('success', 'Slot created successfully.');
    }

    /**
     * Create a new PON port for the specified slot.
     */
    public function createPort(OltSlot $slot): View
    {
        $slot->load(['olt', 'ponPorts']);
        return view('outages::olts.create-port', compact('slot'));
    }

    /**
     * Store a new PON port for the specified slot.
     */
    public function storePort(Request $request, OltSlot $slot): RedirectResponse
    {
        $validated = $request->validate([
            'pon_port_number' => 'required|integer|min:0|max:15|unique:pon_ports,pon_port_number,NULL,id,olt_slot_id,' . $slot->id,
            'pon_port_type' => 'nullable|string|in:GPON,XGSPON,EPON,10G-EPON',
            'status' => 'required|in:active,inactive,faulty,spare'
        ]);

        $validated['olt_slot_id'] = $slot->id;
        $validated['created_by'] = Auth::id();

        PonPort::create($validated);

        return redirect()->route('olts.show', $slot->olt)
            ->with('success', 'PON Port created successfully.');
    }

    /**
     * Get slots for a specific OLT (AJAX endpoint).
     */
    public function getSlots(Olt $olt)
    {
        $slots = $olt->slots()->active()->get(['id', 'slot_number', 'slot_type']);
        
        return response()->json($slots);
    }

    /**
     * Get PON ports for a specific slot (AJAX endpoint).
     */
    public function getPorts(OltSlot $slot)
    {
        $ports = $slot->ponPorts()->active()->get(['id', 'pon_port_number', 'pon_port_type']);
        
        return response()->json($ports);
    }

    /**
     * Show the form for editing a slot.
     */
    public function editSlot(OltSlot $slot): View
    {
        $slot->load('olt');
        
        return view('outages::olts.edit-slot', compact('slot'));
    }

    /**
     * Update the specified slot.
     */
    public function updateSlot(Request $request, OltSlot $slot): RedirectResponse
    {
        $validated = $request->validate([
            'slot_number' => 'required|integer|min:0|max:31|unique:olt_slots,slot_number,' . $slot->id . ',id,olt_id,' . $slot->olt_id,
            'slot_type' => 'nullable|string|in:GPON,XGSPON,EPON,10G-EPON',
            'status' => 'required|in:active,inactive,faulty,spare'
        ]);

        $validated['edited_by'] = Auth::id();

        $slot->update($validated);

        return redirect()->route('olts.show', $slot->olt)
            ->with('success', 'Slot updated successfully.');
    }

    /**
     * Show the form for editing a PON port.
     */
    public function editPort(PonPort $port): View
    {
        $port->load(['oltSlot.olt']);
        
        return view('outages::olts.edit-port', compact('port'));
    }

    /**
     * Update the specified PON port.
     */
    public function updatePort(Request $request, PonPort $port): RedirectResponse
    {
        $validated = $request->validate([
            'pon_port_number' => 'required|integer|min:0|max:15|unique:pon_ports,pon_port_number,' . $port->id . ',id,olt_slot_id,' . $port->olt_slot_id,
            'pon_port_type' => 'nullable|string|in:GPON,XGSPON,EPON,10G-EPON',
            'status' => 'required|in:active,inactive,faulty,spare'
        ]);

        $validated['edited_by'] = Auth::id();

        $port->update($validated);

        return redirect()->route('olts.show', $port->oltSlot->olt)
            ->with('success', 'PON Port updated successfully.');
    }
}
