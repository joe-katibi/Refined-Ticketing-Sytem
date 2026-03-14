<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TeamType;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    /**
     * Display a listing of the teams.
     */
    public function index()
    {
        $teams = Team::with(['teamType', 'partner', 'creator', 'editor'])
            ->latest()
            ->paginate(10);

        $teamTypes = \App\Models\TeamType::all();
        $partners = \App\Models\Partner::where('status', 'active')->get();
        $inhouseTechnicians = \App\Models\User::role('inhouse-technician')->get();

        return view('teams.index', compact('teams', 'teamTypes', 'partners', 'inhouseTechnicians'));
    }

    /**
     * Store a newly created team in storage.
     */
    public function store(Request $request)
    {
        try {
            // Get the team type
            $teamType = \App\Models\TeamType::findOrFail($request->team_type_id);
            $isInhouse = strtolower($teamType->name) === 'inhouse';

            $rules = [
                'team_type_id' => 'required|exists:team_types,id',
                'team_name' => 'required|string|max:255|unique:teams,team_name',
                'description' => 'nullable|string',
                'status' => 'required|in:active,inactive',
            ];

            // Add conditional validation rules
            if ($isInhouse) {
                $rules['users'] = 'required|array|min:1';
                $rules['users.*'] = 'exists:users,id';
            } else {
                $rules['partner_id'] = 'required|exists:partners,id';
            }

            $validated = $request->validate($rules);

            // For inhouse teams, set a default partner or get from config
            $partnerId = $isInhouse 
                ? \App\Models\Partner::where('is_default', true)->value('id') 
                : $validated['partner_id'];

            if ($isInhouse && !$partnerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No default partner found for inhouse team.'
                ], 422);
            }

            // Create the team
            $team = Team::create([
                'team_type_id' => $validated['team_type_id'],
                'partner_id' => $partnerId,
                'team_name' => $validated['team_name'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
                'created_by' => Auth::id(),
                'edited_by' => Auth::id(),
            ]);

            // Attach users if this is an inhouse team
            if ($isInhouse && !empty($validated['users'])) {
                $team->users()->sync($validated['users']);
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Team created successfully!',
                    'data' => $team->load('teamType', 'partner')
                ]);
            }

            return redirect()->route('teams.index')
                ->with('success', 'Team created successfully!');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => $e->errors() ?? null
                ], 422);
            }
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Update the specified team in storage.
     */
    public function update(Request $request, Team $team)
    {
        try {
            $validated = $request->validate([
                'team_type_id' => 'required|exists:team_types,id',
                'partner_id' => 'required|exists:partners,id',
                'team_name' => 'required|string|max:255|unique:teams,team_name,' . $team->id,
                'description' => 'nullable|string',
                'status' => 'required|in:active,inactive',
            ]);

            $team->update([
                'team_type_id' => $validated['team_type_id'],
                'partner_id' => $validated['partner_id'],
                'team_name' => $validated['team_name'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
                'edited_by' => Auth::id(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Team updated successfully!',
                    'data' => $team->load('teamType', 'partner')
                ]);
            }

            return redirect()->route('teams.index')
                ->with('success', 'Team updated successfully!');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => $e->errors() ?? null
                ], 422);
            }
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified team from storage.
     */
    public function destroy(Team $team)
    {
        try {
            $team->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Team deleted successfully!'
                ]);
            }

            return redirect()->route('teams.index')
                ->with('success', 'Team deleted successfully!');

        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get teams by partner ID
     */
    public function getByPartner($partnerId)
    {
        $teams = Team::where('partner_id', $partnerId)
            ->where('status', 'active')
            ->get(['id', 'team_name']);

        return response()->json($teams);
    }
    
    /**
     * Get all active teams for dropdown
     */
    public function getAllTeams()
    {
        $teams = Team::where('status', 'active')
            ->get(['id', 'team_name']);
            
        return response()->json($teams);
    }
}
