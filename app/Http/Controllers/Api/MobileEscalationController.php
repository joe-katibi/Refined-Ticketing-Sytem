<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Escalations\Entities\Escalation;
use Modules\Escalations\Entities\EscalationHistory;

class MobileEscalationController extends Controller
{
    /**
     * Get escalations for sales team (view only)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Sales team can only see their own escalations
        $escalations = Escalation::with(['department', 'subDepartment', 'user'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'escalations' => $escalations->items(),
            'pagination' => [
                'current_page' => $escalations->currentPage(),
                'last_page' => $escalations->lastPage(),
                'per_page' => $escalations->perPage(),
                'total' => $escalations->total(),
            ]
        ]);
    }

    /**
     * Create new sales escalation
     */
    public function store(Request $request)
    {
        $user = $request->user();
        
        // Only sales team can create escalations
        if (!$user->hasRole('escalations-agent')) {
            return response()->json([
                'message' => 'Access denied. Only sales team can create escalations.'
            ], 403);
        }

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'issue_description' => 'required|string',
            'priority' => 'required|in:low,medium,high,critical',
            'department_id' => 'required|exists:departments,id',
            'sub_department_id' => 'nullable|exists:sub_departments,id',
            'location' => 'nullable|string|max:255',
        ]);

        $escalation = Escalation::create([
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email,
            'issue_description' => $request->issue_description,
            'priority' => $request->priority,
            'status' => 'open',
            'department_id' => $request->department_id,
            'sub_department_id' => $request->sub_department_id,
            'location' => $request->location,
            'user_id' => $user->id,
        ]);

        // Create history record
        EscalationHistory::create([
            'escalation_id' => $escalation->id,
            'user_id' => $user->id,
            'action' => 'created',
            'old_values' => null,
            'new_values' => json_encode($escalation->toArray()),
            'notes' => 'Escalation created via mobile app',
        ]);

        return response()->json([
            'message' => 'Escalation created successfully',
            'escalation' => $escalation->load(['department', 'subDepartment'])
        ], 201);
    }

    /**
     * Get specific escalation details
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        
        $escalation = Escalation::with(['department', 'subDepartment', 'user'])
            ->where('user_id', $user->id)
            ->findOrFail($id);

        return response()->json([
            'escalation' => $escalation
        ]);
    }

    /**
     * Get escalation history
     */
    public function history(Request $request, $id)
    {
        $user = $request->user();
        
        $escalation = Escalation::where('user_id', $user->id)->findOrFail($id);

        $history = EscalationHistory::with('user')
            ->where('escalation_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'history' => $history
        ]);
    }

    /**
     * Get departments for escalation creation
     */
    public function departments(Request $request)
    {
        $departments = \DB::table('departments')
            ->select('id', 'name')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return response()->json([
            'departments' => $departments
        ]);
    }

    /**
     * Get sub-departments for selected department
     */
    public function subDepartments(Request $request, $departmentId)
    {
        $subDepartments = \DB::table('sub_departments')
            ->select('id', 'name')
            ->where('department_id', $departmentId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return response()->json([
            'sub_departments' => $subDepartments
        ]);
    }

    /**
     * Get user's escalation performance metrics
     */
    public function performance(Request $request)
    {
        $user = $request->user();
        
        $totalEscalations = Escalation::where('user_id', $user->id)->count();
        $openEscalations = Escalation::where('user_id', $user->id)
            ->where('status', 'open')->count();
        $closedEscalations = Escalation::where('user_id', $user->id)
            ->where('status', 'closed')->count();
        
        $closureRate = $totalEscalations > 0 ? 
            round(($closedEscalations / $totalEscalations) * 100, 2) : 0;

        return response()->json([
            'performance' => [
                'total_escalations' => $totalEscalations,
                'open_escalations' => $openEscalations,
                'closed_escalations' => $closedEscalations,
                'closure_rate' => $closureRate,
            ]
        ]);
    }
}
