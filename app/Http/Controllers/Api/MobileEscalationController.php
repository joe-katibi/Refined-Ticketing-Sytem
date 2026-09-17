<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Escalations\App\Models\Escalation;
use Modules\Escalations\App\Models\EscalationHistory;

/**
 * This entire controller previously targeted a schema that does not exist —
 * `escalations.user_id`, `customer_name`, `customer_phone`, `issue_description`,
 * `location`, a `user` relation, `status` values like 'open'/'closed', and
 * `departments.name`/`status='active'` (the real columns are
 * `department_name`/`department_status`, an integer). Every method here 500'd
 * on every call. Rewritten to match the real `escalations` table (as used by
 * the working web create flow in Modules\Escalations\Http\Controllers\ListController)
 * and the real `escalation_histories` schema.
 *
 * Uses Modules\Escalations\App\Models\Escalation, NOT
 * Modules\Escalations\Entities\Escalation — the latter has a boot() hook that
 * auto-generates `escalation_id` as a string ticket number ("ESC-N"), but the
 * real `escalations.escalation_id` column is an integer FK to
 * `escalation_lists.id` (see ListController::store()); creating through the
 * Entities class throws "Incorrect integer value: 'ESC-2'". The App\Models
 * class has no such hook and matches what the working web flow actually uses.
 */
class MobileEscalationController extends Controller
{
    /**
     * List escalations created by the current mobile user.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $escalations = Escalation::with(['department', 'subDepartment', 'subcategory'])
            ->where('created_by', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'escalations' => $escalations->items(),
            'pagination' => [
                'current_page' => $escalations->currentPage(),
                'last_page' => $escalations->lastPage(),
                'per_page' => $escalations->perPage(),
                'total' => $escalations->total(),
            ],
        ]);
    }

    /**
     * Create a new escalation from the mobile app.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'account_number' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'required|exists:subcategories,id',
            'department_id' => 'required|exists:departments,id',
            'sub_department_id' => 'nullable|exists:sub_departments,id',
            'description' => 'required|string',
            'priority' => 'required|in:Low,Medium,High',
        ]);

        // Generated BEFORE the transaction below on purpose — see the identical
        // fix/comment in Modules\Escalations\Http\Controllers\ListController::store().
        $ticketNumber = \App\Services\SequenceNumberService::next('escalation:ESC');
        $ticketId = 'ESC-'.$ticketNumber;

        $escalation = \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $user, $ticketId) {
            $escalation = Escalation::create(array_merge($validated, [
                'ticket_id' => $ticketId,
                'status' => 'Escalated-Open',
                'assigned_to' => null,
                'created_by' => $user->id,
            ]));

            EscalationHistory::create([
                'escalation_id' => $escalation->id,
                'ticket_id' => $ticketId,
                'status' => 'Escalated-Open',
                'department_id' => $validated['department_id'],
                'category_id' => $validated['category_id'],
                'sub_category_id' => $validated['sub_category_id'],
                'description' => $validated['description'],
                'account_number' => $validated['account_number'],
                'priority' => $validated['priority'],
                'sub_department_id' => $validated['sub_department_id'] ?? null,
                'action_by' => $user->id,
            ]);

            (new \App\Services\Fifo\FifoQueueService)->enqueue(
                'escalation',
                'escalation',
                $escalation->id,
                $validated['priority']
            );

            return $escalation;
        });

        return response()->json([
            'message' => 'Escalation created successfully',
            'escalation' => $escalation->load(['department', 'subDepartment']),
        ], 201);
    }

    /**
     * Get specific escalation details (only if the mobile user created it).
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $escalation = Escalation::with(['department', 'subDepartment', 'subcategory'])
            ->where('created_by', $user->id)
            ->findOrFail($id);

        return response()->json([
            'escalation' => $escalation,
        ]);
    }

    /**
     * Get escalation history.
     */
    public function history(Request $request, $id)
    {
        $user = $request->user();

        Escalation::where('created_by', $user->id)->findOrFail($id);

        $history = EscalationHistory::with('actionBy')
            ->where('escalation_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'history' => $history,
        ]);
    }

    /**
     * Get departments for escalation creation.
     */
    public function departments(Request $request)
    {
        $departments = \DB::table('departments')
            ->select('id', 'department_name as name')
            ->where('department_status', 1)
            ->orderBy('department_name')
            ->get();

        return response()->json([
            'departments' => $departments,
        ]);
    }

    /**
     * Get sub-departments for the selected department.
     */
    public function subDepartments(Request $request, $departmentId)
    {
        $subDepartments = \DB::table('sub_departments')
            ->select('id', 'sub_department_name as name')
            ->where('department_id', $departmentId)
            ->where('sub_department_status', 1)
            ->orderBy('sub_department_name')
            ->get();

        return response()->json([
            'sub_departments' => $subDepartments,
        ]);
    }

    /**
     * Get categories for escalation creation. The mobile create-escalation
     * form has always required category_id/sub_category_id (see store()
     * above), but there was no endpoint anywhere for the app to fetch the
     * list of valid categories — it could never populate that dropdown.
     */
    public function categories(Request $request)
    {
        $categories = \DB::table('categories')
            ->select('id', 'category_name as name')
            ->where('status', 'Active')
            ->orderBy('category_name')
            ->get();

        return response()->json([
            'categories' => $categories,
        ]);
    }

    /**
     * Get subcategories for the selected category.
     */
    public function subCategories(Request $request, $categoryId)
    {
        $subCategories = \DB::table('subcategories')
            ->select('id', 'sub_category_name as name')
            ->where('category_id', $categoryId)
            ->where('status', 'Active')
            ->orderBy('sub_category_name')
            ->get();

        return response()->json([
            'sub_categories' => $subCategories,
        ]);
    }

    /**
     * Get the mobile user's own escalation performance metrics.
     */
    public function performance(Request $request)
    {
        $user = $request->user();

        $totalEscalations = Escalation::where('created_by', $user->id)->count();
        $openEscalations = Escalation::where('created_by', $user->id)
            ->where('status', 'Escalated-Open')->count();
        $closedEscalations = Escalation::where('created_by', $user->id)
            ->where('status', 'Escalated-Closed')->count();

        $closureRate = $totalEscalations > 0
            ? round(($closedEscalations / $totalEscalations) * 100, 2)
            : 0;

        return response()->json([
            'performance' => [
                'total_escalations' => $totalEscalations,
                'open_escalations' => $openEscalations,
                'closed_escalations' => $closedEscalations,
                'closure_rate' => $closureRate,
            ],
        ]);
    }
}
