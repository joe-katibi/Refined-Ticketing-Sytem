<?php

namespace Modules\Appointment\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Appointment\Models\AppointmentStatus;

class AppointmentStatusController extends Controller
{
    /**
     * Constructor with middleware
     */
    public function __construct()
    {
        // Uncomment and adjust permissions as needed
        // $this->middleware('permission:view-appointment-statuses')->only(['index', 'show']);
        // $this->middleware('permission:create-appointment-statuses')->only(['create', 'store']);
        // $this->middleware('permission:edit-appointment-statuses')->only(['edit', 'update']);
        // $this->middleware('permission:delete-appointment-statuses')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $statuses = AppointmentStatus::orderBy('sort_order')
            ->orderBy('display_name')
            ->paginate(15);
            
        return view('appointment::status.index', compact('statuses'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('appointment::status.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:appointment_statuses,name',
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'color' => 'required|string|max:20',
            'badge_class' => 'nullable|string|max:50',
            'status' => 'required|in:Active,Inactive',
            'sort_order' => 'nullable|integer',
        ]);

        try {
            $validated['created_by'] = Auth::id();
            
            AppointmentStatus::create($validated);
            
            return redirect()->route('appointment.statuses.index')
                ->with('success', 'Appointment status created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating appointment status: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create appointment status. ' . $e->getMessage());
        }
    }

    /**
     * Show the specified resource.
     * @param AppointmentStatus $status
     * @return Renderable
     */
    public function show(AppointmentStatus $status)
    {
        return view('appointment::status.show', compact('status'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param AppointmentStatus $status
     * @return Renderable
     */
    public function edit(AppointmentStatus $status)
    {
        return view('appointment::status.edit', compact('status'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param AppointmentStatus $status
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, AppointmentStatus $status)
    {
        $uniqueRule = 'unique:appointment_statuses,name,' . $status->id;
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', $uniqueRule],
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'color' => 'required|string|max:20',
            'badge_class' => 'nullable|string|max:50',
            'status' => 'required|in:Active,Inactive',
            'sort_order' => 'nullable|integer',
        ]);

        try {
            // Don't allow changing system statuses
            if ($status->is_system && $status->name != $validated['name']) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Cannot change the name of system-defined statuses.');
            }
            
            $validated['edited_by'] = Auth::id();
            
            $status->update($validated);
            
            return redirect()->route('appointment.statuses.index')
                ->with('success', 'Appointment status updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating appointment status: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update appointment status. ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param AppointmentStatus $status
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(AppointmentStatus $status)
    {
        try {
            // Don't allow deleting system statuses
            if ($status->is_system) {
                return redirect()->back()
                    ->with('error', 'Cannot delete system-defined statuses.');
            }
            
            // Check if status is in use
            if ($status->appointments()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Cannot delete status that is in use by appointments.');
            }
            
            $status->delete();
            
            return redirect()->route('appointment.statuses.index')
                ->with('success', 'Appointment status deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting appointment status: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to delete appointment status. ' . $e->getMessage());
        }
    }
    
    /**
     * Toggle the status between Active and Inactive
     * @param AppointmentStatus $status
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleStatus(AppointmentStatus $status)
    {
        try {
            $newStatus = $status->status === 'Active' ? 'Inactive' : 'Active';
            $status->update([
                'status' => $newStatus,
                'edited_by' => Auth::id()
            ]);
            
            return redirect()->route('appointment.statuses.index')
                ->with('success', "Status changed to {$newStatus} successfully.");
        } catch (\Exception $e) {
            Log::error('Error toggling appointment status: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to toggle status. ' . $e->getMessage());
        }
    }
}
