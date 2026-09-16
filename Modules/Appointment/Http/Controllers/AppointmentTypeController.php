<?php

namespace Modules\Appointment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Appointment\Models\AppointmentType;
use Modules\Appointment\Models\SubAppointmentType;
use Illuminate\Support\Str;

class AppointmentTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $appointmentTypes = AppointmentType::with(['subTypes', 'createdBy', 'updatedBy'])->latest()->get();
        return view('appointment::appointmentType.index', compact('appointmentTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('appointment::appointmentType.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type_name' => 'required|string|max:255|unique:appointment_types,type_name',
            'code_prefix' => 'required|string|max:10|alpha_num|unique:appointment_types,code_prefix',
            'type_description' => 'nullable|string',
            'type_status' => 'required|in:Active,Inactive',
        ]);

        $validated['code_prefix'] = strtoupper($validated['code_prefix']);
        $validated['created_by'] = auth()->id();
        $validated['edited_by'] = auth()->id();

        AppointmentType::create($validated);

        return redirect()->route('appointment.types.index')
            ->with('success', 'Appointment type created successfully.');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $appointmentType = AppointmentType::with(['subTypes', 'createdBy', 'updatedBy'])->findOrFail($id);
        return view('appointment::appointmentType.show', compact('appointmentType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $appointmentType = AppointmentType::findOrFail($id);
        return view('appointment::appointmentType.edit', compact('appointmentType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $appointmentType = AppointmentType::findOrFail($id);

        $validated = $request->validate([
            'type_name' => 'required|string|max:255|unique:appointment_types,type_name,' . $id,
            'code_prefix' => 'required|string|max:10|alpha_num|unique:appointment_types,code_prefix,' . $id,
            'type_description' => 'nullable|string',
            'type_status' => 'required|in:Active,Inactive',
        ]);

        $validated['code_prefix'] = strtoupper($validated['code_prefix']);
        $validated['edited_by'] = auth()->id();

        $appointmentType->update($validated);

        return redirect()->route('appointment.types.index')
            ->with('success', 'Appointment type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $appointmentType = AppointmentType::findOrFail($id);
        
        // Check if there are any sub-appointment types
        if ($appointmentType->subTypes()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete appointment type with existing sub-types.');
        }
        
        $appointmentType->delete();

        return redirect()->route('appointment.types.index')
            ->with('success', 'Appointment type deleted successfully.');
    }

    /**
     * Show the form for creating a new sub-appointment type.
     */
    public function createSubType($appointmentTypeId)
    {
        $appointmentType = AppointmentType::findOrFail($appointmentTypeId);
        return view('appointment::appointmentType.sub-type.create', compact('appointmentType'));
    }

    /**
     * Store a newly created sub-appointment type in storage.
     */
    public function storeSubType(Request $request, $appointmentTypeId)
    {
        $appointmentType = AppointmentType::findOrFail($appointmentTypeId);

        $validated = $request->validate([
            'sub_type_name' => 'required|string|max:255|unique:sub_appointment_types,sub_type_name',
            'sub_type_description' => 'nullable|string',
            'sub_type_status' => 'required|in:Active,Inactive',
        ]);

        $validated['appointment_type_id'] = $appointmentType->id;
        $validated['created_by'] = auth()->id();
        $validated['edited_by'] = auth()->id();

        SubAppointmentType::create($validated);

        return redirect()->route('appointment.types.show', $appointmentType->id)
            ->with('success', 'Sub-appointment type created successfully.');
    }

    /**
     * Show the form for editing the specified sub-appointment type.
     */
    public function editSubType($appointmentTypeId, $subTypeId)
    {
        $appointmentType = AppointmentType::findOrFail($appointmentTypeId);
        $subType = SubAppointmentType::where('appointment_type_id', $appointmentTypeId)
            ->findOrFail($subTypeId);
            
        return view('appointment::appointmentType.sub-type.edit', compact('appointmentType', 'subType'));
    }

    /**
     * Update the specified sub-appointment type in storage.
     */
    public function updateSubType(Request $request, $appointmentTypeId, $subTypeId)
    {
        $subType = SubAppointmentType::where('appointment_type_id', $appointmentTypeId)
            ->findOrFail($subTypeId);

        $validated = $request->validate([
            'sub_type_name' => 'required|string|max:255|unique:sub_appointment_types,sub_type_name,' . $subTypeId,
            'sub_type_description' => 'nullable|string',
            'sub_type_status' => 'required|in:Active,Inactive',
        ]);

        $validated['edited_by'] = auth()->id();

        $subType->update($validated);

        return redirect()->route('appointment.types.show', $appointmentTypeId)
            ->with('success', 'Sub-appointment type updated successfully.');
    }

    /**
     * Remove the specified sub-appointment type from storage.
     */
    public function destroySubType($appointmentTypeId, $subTypeId)
    {
        $subType = SubAppointmentType::where('appointment_type_id', $appointmentTypeId)
            ->findOrFail($subTypeId);
            
        $subType->delete();

        return redirect()->route('appointment.types.show', $appointmentTypeId)
            ->with('success', 'Sub-appointment type deleted successfully.');
    }
}
