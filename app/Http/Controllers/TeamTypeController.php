<?php

namespace App\Http\Controllers;

use App\Models\TeamType;
use App\Models\SubTeamType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class TeamTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $teamTypes = TeamType::with(['creator', 'editor', 'subTypes', 'department', 'subDepartment'])
            ->latest()
            ->get();

        return view('teamtypes.index', compact('teamTypes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $departments = \App\Models\Department::where('department_status', 1)->get();
        $subDepartments = \App\Models\SubDepartment::where('sub_department_status', 1)->get();
        
        return view('teamtypes.create', compact('departments', 'subDepartments'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'type_name' => 'required|string|max:255|unique:team_types,type_name',
                'description' => 'nullable|string',
                'status' => 'required|in:Active,Inactive',
                'department_id' => 'nullable|exists:departments,id',
                'sub_department_id' => 'nullable|exists:sub_departments,id',
            ]);

            $teamType = TeamType::create([
                'type_name' => $validated['type_name'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
                'department_id' => $validated['department_id'] ?? null,
                'sub_department_id' => $validated['sub_department_id'] ?? null,
                'created_by' => Auth::id(),
            ]);


            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Team type created successfully.',
                    'data' => $teamType
                ]);
            }

            return redirect()->route('teamtypes.index')
                ->with('success', 'Team type created successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors(),
                    'message' => 'Validation failed.'
                ], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while creating the team type.',
                    'error' => $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'An error occurred while creating the team type.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TeamType  $teamtype
     * @param  \App\Models\TeamType  $teamType
     * @return \Illuminate\Http\Response
     */
    public function show(TeamType $teamtype)
    {
        $teamtype->load(['subTypes', 'creator', 'editor']);
       // dd($teamtype);
        return view('teamtypes.show', compact('teamtype'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TeamType  $teamtype
     * @return \Illuminate\Http\Response
     */
    public function edit(TeamType $teamtype)
    {
        $departments = \App\Models\Department::where('department_status', 1)->get();
        $subDepartments = \App\Models\SubDepartment::where('sub_department_status', 1)->get();
        
        return view('teamtypes.edit', compact('teamtype', 'departments', 'subDepartments'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TeamType  $teamType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TeamType $teamtype)
    {
        try {
            $validated = $request->validate([
                'type_name' => 'required|string|max:255|unique:team_types,type_name,' . $teamtype->id,
                'description' => 'nullable|string',
                'status' => 'required|in:Active,Inactive',
                'department_id' => 'nullable|exists:departments,id',
                'sub_department_id' => 'nullable|exists:sub_departments,id',
            ]);

            $teamtype->update([
                'type_name' => $validated['type_name'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
                'department_id' => $validated['department_id'] ?? null,
                'sub_department_id' => $validated['sub_department_id'] ?? null,
                'edited_by' => Auth::id(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Team type updated successfully.',
                    'data' => $teamtype->load('creator', 'editor')
                ]);
            }

            return redirect()->route('teamtypes.index')
                ->with('success', 'Team type updated successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors(),
                    'message' => 'Validation failed.'
                ], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while updating the team type.',
                    'error' => $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'An error occurred while updating the team type.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TeamType  $teamType
     * @return \Illuminate\Http\Response
     */
    public function destroy(TeamType $teamtype)
    {
        $teamtype->delete();
        return redirect()->route('teamtypes.index')
            ->with('success', 'Team type deleted successfully.');
    }

    /**
     * Show the form for creating a new sub-team type.
     *
     * @param  \App\Models\TeamType  $teamtype
     * @return \Illuminate\Http\Response
     */
    public function createSubType(TeamType $teamtype)
    {
        $departments = \App\Models\Department::where('department_status', 1)->get();
        $subDepartments = \App\Models\SubDepartment::where('sub_department_status', 1)->get();
        
        return view('teamtypes.subtypes.create', compact('teamtype', 'departments', 'subDepartments'));
    }

    /**
     * Store a newly created sub-team type in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TeamType  $teamtype
     * @return \Illuminate\Http\Response
     */
    public function storeSubType(Request $request, TeamType $teamtype)
    {
        try {
            \Log::info('Store Sub-Type Request Data:', $request->all());
            \Log::info('TeamType ID:', ['id' => $teamtype->id]);

            $validated = $request->validate([
                'sub_type_name' => 'required|string|max:255|unique:sub_team_types,sub_type_name',
                'sub_type_description' => 'nullable|string',
                'sub_type_status' => 'required|in:Active,Inactive',
                'department_id' => 'nullable|exists:departments,id',
                'sub_department_id' => 'nullable|exists:sub_departments,id',
            ]);

            \Log::info('Validated Data:', $validated);

            $subType = $teamtype->subTypes()->create([
                'sub_type_name' => $validated['sub_type_name'],
                'sub_type_description' => $validated['sub_type_description'] ?? null,
                'sub_type_status' => $validated['sub_type_status'],
                'department_id' => $validated['department_id'] ?? null,
                'sub_department_id' => $validated['sub_department_id'] ?? null,
                'created_by' => Auth::id(),
            ]);

            \Log::info('Sub-Team Type Created:', $subType->toArray());

            return redirect()->route('teamtypes.show', $teamtype)
                ->with('success', 'Sub-team type created successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation Error:', $e->errors());
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Error creating sub-team type:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->with('error', 'An error occurred while creating the sub-team type: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified sub-team type.
     *
     * @param  \App\Models\TeamType  $teamtype
     * @param  \App\Models\SubTeamType  $subtype
     * @return \Illuminate\Http\Response
     */
    public function editSubType(TeamType $teamtype, SubTeamType $subtype)
    {
        $departments = \App\Models\Department::where('department_status', 1)->get();
        $subDepartments = \App\Models\SubDepartment::where('sub_department_status', 1)->get();
        
        return view('teamtypes.subtypes.edit', compact('teamtype', 'subtype', 'departments', 'subDepartments'));
    }

    /**
     * Update the specified sub-team type in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TeamType  $teamtype
     * @param  \App\Models\SubTeamType  $subtype
     * @return \Illuminate\Http\Response
     */
    public function updateSubType(Request $request, TeamType $teamtype, SubTeamType $subtype)
    {
        try {
            $validated = $request->validate([
                'sub_type_name' => 'required|string|max:255|unique:sub_team_types,sub_type_name,' . $subtype->id,
                'sub_type_description' => 'nullable|string',
                'sub_type_status' => 'required|in:Active,Inactive',
                'department_id' => 'nullable|exists:departments,id',
                'sub_department_id' => 'nullable|exists:sub_departments,id',
            ]);

            $subtype->update([
                'sub_type_name' => $validated['sub_type_name'],
                'sub_type_description' => $validated['sub_type_description'] ?? null,
                'sub_type_status' => $validated['sub_type_status'],
                'department_id' => $validated['department_id'] ?? null,
                'sub_department_id' => $validated['sub_department_id'] ?? null,
                'edited_by' => Auth::id(),
            ]);

            return redirect()->route('teamtypes.show', $teamtype)
                ->with('success', 'Sub-team type updated successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while updating the sub-team type.');
        }
    }

    /**
     * Remove the specified sub-team type from storage.
     *
     * @param  \App\Models\TeamType  $teamtype
     * @param  \App\Models\SubTeamType  $subtype
     * @return \Illuminate\Http\Response
     */
    public function destroySubType(TeamType $teamtype, SubTeamType $subtype)
    {
        $subtype->delete();
        return redirect()->route('teamtypes.show', $teamtype)
            ->with('success', 'Sub-team type deleted successfully.');
    }

    /**
     * Get sub departments by department ID
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubDepartments(Request $request)
    {
        try {
            $departmentId = $request->get('department_id');
            
            if (!$departmentId) {
                return response()->json([]);
            }
            
            $subDepartments = \App\Models\SubDepartment::where('department_id', $departmentId)
                ->where('sub_department_status', 1)
                ->get(['id', 'sub_department_name as name']);
                
            return response()->json($subDepartments);
        } catch (\Exception $e) {
            Log::error('Error fetching sub departments: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }

    /**
     * Get team types by department ID
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTeamTypesByDepartment(Request $request)
    {
        try {
            $departmentId = $request->get('department_id');
            
            if (!$departmentId) {
                return response()->json([]);
            }
            
            $teamTypes = TeamType::where('department_id', $departmentId)
                ->where('status', 'Active')
                ->get(['id', 'type_name']);
                
            return response()->json($teamTypes);
        } catch (\Exception $e) {
            \Log::error('Error fetching team types by department: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }

    /**
     * Get sub-teams by team type ID
     *
     * @param  \App\Models\TeamType  $teamtype
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubTeams(TeamType $teamtype)
    {
        try {
            $teamTypeId = $teamtype->id;
        // Log the incoming request
        Log::info('Fetching sub-teams for team type ID: ' . $teamTypeId);

        // Get sub-teams for the given team type ID
        $subTeams = SubTeamType::where('team_type_id', $teamTypeId)
            // Remove the status filter to get all sub-teams
            // ->where('sub_type_status', 'Active')
            ->get(['id', 'sub_type_name as name', 'sub_type_status as status']);

        Log::info('Found ' . $subTeams->count() . ' sub-teams for team type ID: ' . $teamTypeId);

        if ($subTeams->isEmpty()) {
            Log::warning('No active sub-teams found for team type ID: ' . $teamTypeId);
        } else {
            Log::debug('Sub-teams found:', $subTeams->toArray());
        }

        return response()->json($subTeams);
    } catch (\Exception $e) {
        Log::error('Error in getSubTeams: ' . $e->getMessage() . '\n' . $e->getTraceAsString());
        return response()->json([
            'error' => 'Failed to load sub-teams',
            'message' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Toggle the status of a team type
     *
     * @param  \App\Models\TeamType  $teamtype
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleStatus(TeamType $teamtype)
    {
        try {
            $newStatus = $teamtype->status === 'Active' ? 'Inactive' : 'Active';
            
            $teamtype->update([
                'status' => $newStatus,
                'updated_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Team type status updated successfully.',
                'new_status' => $newStatus,
                'status_badge' => $teamtype->status_badge
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling team type status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update team type status.'
            ], 500);
        }
    }

    /**
     * Toggle the status of a sub team type
     *
     * @param  \App\Models\TeamType  $teamtype
     * @param  \App\Models\SubTeamType  $subtype
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleSubTypeStatus(TeamType $teamtype, SubTeamType $subtype)
    {
        try {
            $newStatus = $subtype->sub_type_status === 'Active' ? 'Inactive' : 'Active';
            
            $subtype->update([
                'sub_type_status' => $newStatus,
                'updated_by' => Auth::id()
            ]);

            // Create status badge HTML
            $statusBadge = $newStatus === 'Active' 
                ? '<span class="badge bg-label-success">Active</span>'
                : '<span class="badge bg-label-danger">Inactive</span>';

            return response()->json([
                'success' => true,
                'message' => 'Sub team type status updated successfully.',
                'new_status' => $newStatus,
                'status_badge' => $statusBadge
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling sub team type status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update sub team type status.'
            ], 500);
        }
    }
}
