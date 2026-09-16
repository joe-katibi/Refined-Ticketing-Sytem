<?php

namespace App\Http\Controllers;

use App\Models\Fifo\AgentWorkload;
use App\Models\Fifo\QueueEntry;
use App\Models\User;
use App\Services\Fifo\FifoQueueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Shared FIFO dispatch console for all three modules (escalation, appointment,
 * outage) — the queue engine itself lives in FifoQueueService; this just
 * exposes it to dispatchers/team leads as "Assign Next" / "Bulk Assign".
 */
class FifoQueueController extends Controller
{
    private const MODULES = ['escalation', 'appointment', 'outage'];

    public function index(string $module)
    {
        abort_unless(in_array($module, self::MODULES), 404);

        $entries = QueueEntry::query()
            ->whereHas('queue', fn ($q) => $q->where('module', $module))
            ->where('status', 'waiting')
            ->with('region')
            ->orderByRaw(
                "CASE priority
                    WHEN 'Critical' THEN 0 WHEN 'High' THEN 1
                    WHEN 'Medium' THEN 2 WHEN 'Low' THEN 3 ELSE 4 END"
            )
            ->orderBy('queue_entered_at')
            ->get()
            ->map(function (QueueEntry $entry) {
                $entry->ticket = $entry->work();

                return $entry;
            });

        $agents = AgentWorkload::where('module', $module)->with('user')->get();

        $technicians = User::orderBy('name')->get(['id', 'name', 'region_id']);

        return view('fifo.index', compact('module', 'entries', 'agents', 'technicians'));
    }

    public function assignNext(string $module, FifoQueueService $fifo)
    {
        abort_unless(in_array($module, self::MODULES), 404);

        $result = $fifo->assignNext($module, Auth::id());

        if (!$result) {
            return back()->with('warning', 'Queue is empty — nothing to assign.');
        }

        if (!$result['agent']) {
            return back()->with('warning', 'Oldest waiting ticket has no eligible/available agent right now. It stays queued.');
        }

        return back()->with('success', 'Assigned ' . ($result['entry']->work_type) . ' #' . $result['entry']->work_id . ' to ' . $result['agent']->user->name . '.');
    }

    public function bulkAssign(string $module, Request $request, FifoQueueService $fifo)
    {
        abort_unless(in_array($module, self::MODULES), 404);

        $validated = $request->validate([
            'entry_ids' => 'required|array|min:1',
            'entry_ids.*' => 'integer',
            'user_id' => 'required|exists:users,id',
        ]);

        $results = $fifo->bulkAssignToUser($module, (int) $validated['user_id'], $validated['entry_ids'], Auth::id());
        $assigned = collect($results)->where('assigned', true)->count();

        return back()->with('success', "Bulk-assigned {$assigned} of " . count($validated['entry_ids']) . ' selected tickets.');
    }

    public function setAgentAvailability(string $module, Request $request)
    {
        abort_unless(in_array($module, self::MODULES), 404);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'is_available' => 'required|boolean',
            'capacity' => 'nullable|integer|min:1',
        ]);

        AgentWorkload::updateOrCreate(
            ['user_id' => $validated['user_id'], 'module' => $module],
            ['is_available' => $validated['is_available'], 'capacity' => $validated['capacity'] ?? null]
        );

        return back()->with('success', 'Agent availability updated.');
    }
}
