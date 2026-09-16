<?php

namespace App\Services\Fifo;

use App\Models\Fifo\AgentWorkload;
use App\Models\Fifo\AssignmentAttempt;
use App\Models\Fifo\QueueEntry;
use App\Models\Fifo\WorkQueue;
use Illuminate\Support\Facades\DB;

/**
 * FIFO work-allocation engine (spec section 5). A ticket becomes eligible for
 * assignment by calling enqueue() when it's created. A dispatcher/team lead
 * (or a scheduled `fifo:dispatch` run) calls assignNext()/bulkAssign() to hand
 * out work: oldest-eligible-first within a priority band, region-aware, and
 * concurrency-safe — the same locked-transaction discipline as the ticket
 * numbering fix, so two dispatchers clicking "Assign Next" at once cannot both
 * grab the same ticket or double-count an agent's workload.
 */
class FifoQueueService
{
    /** Lower number = more urgent. */
    private const PRIORITY_RANK = ['Critical' => 0, 'High' => 1, 'Medium' => 2, 'Low' => 3];

    public function queueFor(string $module): WorkQueue
    {
        return WorkQueue::firstOrCreate(
            ['module' => $module],
            ['name' => ucfirst($module) . ' Queue', 'is_active' => true]
        );
    }

    public function enqueue(string $module, string $workType, int $workId, ?string $priority = null, ?int $regionId = null): QueueEntry
    {
        $queue = $this->queueFor($module);

        return QueueEntry::create([
            'work_queue_id' => $queue->id,
            'work_type' => $workType,
            'work_id' => $workId,
            'priority' => $priority ?: 'Medium',
            'region_id' => $regionId,
            'status' => 'waiting',
            'queue_entered_at' => now(),
        ]);
    }

    /**
     * Assign the single oldest eligible waiting entry in this module's queue.
     * Returns ['entry' => QueueEntry, 'agent' => AgentWorkload|null].
     * A null 'agent' means the oldest entry has no eligible agent right now —
     * it stays in the queue (not skipped) rather than being force-assigned.
     */
    public function assignNext(string $module, ?int $actorId = null): ?array
    {
        return DB::transaction(function () use ($module, $actorId) {
            $entry = QueueEntry::query()
                ->whereHas('queue', fn ($q) => $q->where('module', $module)->where('is_active', true))
                ->where('status', 'waiting')
                ->orderByRaw(
                    "CASE priority
                        WHEN 'Critical' THEN 0
                        WHEN 'High' THEN 1
                        WHEN 'Medium' THEN 2
                        WHEN 'Low' THEN 3
                        ELSE 4 END"
                )
                ->orderBy('queue_entered_at')
                ->lockForUpdate()
                ->first();

            if (!$entry) {
                return null;
            }

            $agent = $this->pickAgent($module, $entry->region_id);

            if (!$agent) {
                AssignmentAttempt::create([
                    'queue_entry_id' => $entry->id,
                    'user_id' => null,
                    'result' => 'no_eligible_agent',
                    'reason' => 'No available agent for this module' . ($entry->region_id ? ' in this region' : ''),
                    'actor_id' => $actorId,
                    'created_at' => now(),
                ]);

                return ['entry' => $entry, 'agent' => null];
            }

            $this->commitAssignment($entry, $agent, $actorId, $actorId ? 'manual_override' : 'assigned');

            return ['entry' => $entry->fresh(), 'agent' => $agent->fresh()];
        });
    }

    /**
     * Bulk-assign up to $limit oldest waiting entries to a single, specific
     * technician (dispatcher-chosen — not FIFO-picked), each still tracked
     * through the same locked/audited path as automatic assignment. Fills the
     * spec gap flagged in QA: "bulk assignment of tickets to a technician".
     */
    public function bulkAssignToUser(string $module, int $userId, array $queueEntryIds, ?int $actorId = null): array
    {
        $results = [];

        foreach ($queueEntryIds as $entryId) {
            $results[] = DB::transaction(function () use ($module, $userId, $entryId, $actorId) {
                $entry = QueueEntry::where('id', $entryId)
                    ->whereHas('queue', fn ($q) => $q->where('module', $module))
                    ->where('status', 'waiting')
                    ->lockForUpdate()
                    ->first();

                if (!$entry) {
                    return ['entry_id' => $entryId, 'assigned' => false, 'reason' => 'Not waiting or not found'];
                }

                $agent = AgentWorkload::firstOrCreate(
                    ['user_id' => $userId, 'module' => $module],
                    ['active_count' => 0, 'is_available' => true]
                );

                $this->commitAssignment($entry, $agent, $actorId, 'manual_override');

                return ['entry_id' => $entryId, 'assigned' => true];
            });
        }

        return $results;
    }

    private function pickAgent(string $module, ?int $regionId): ?AgentWorkload
    {
        $query = AgentWorkload::query()
            ->where('module', $module)
            ->where('is_available', true)
            ->where(fn ($q) => $q->whereNull('capacity')->orWhereColumn('active_count', '<', 'capacity'));

        if ($regionId) {
            // Prefer an agent covering this ticket's region; fall back to any
            // available agent if nobody in that region is free, rather than
            // leaving the ticket stuck unassigned.
            $regional = (clone $query)
                ->whereHas('user', fn ($q) => $q->where('region_id', $regionId))
                ->orderBy('active_count')
                ->orderBy('last_assigned_at')
                ->lockForUpdate()
                ->first();

            if ($regional) {
                return $regional;
            }
        }

        return $query->orderBy('active_count')->orderBy('last_assigned_at')->lockForUpdate()->first();
    }

    private function commitAssignment(QueueEntry $entry, AgentWorkload $agent, ?int $actorId, string $result): void
    {
        $entry->update([
            'status' => 'assigned',
            'assigned_to' => $agent->user_id,
            'assigned_at' => now(),
        ]);

        $agent->increment('active_count');
        $agent->update(['last_assigned_at' => now()]);

        AssignmentAttempt::create([
            'queue_entry_id' => $entry->id,
            'user_id' => $agent->user_id,
            'result' => $result,
            'reason' => $result === 'manual_override' ? 'Assigned by dispatcher' : null,
            'actor_id' => $actorId,
            'created_at' => now(),
        ]);

        $this->applyAssignmentToTicket($entry);
    }

    /**
     * Keeps the underlying ticket's own assigned_to column (used everywhere
     * else in the app — list views, "My Appointments", etc.) in sync with the
     * queue's decision.
     */
    private function applyAssignmentToTicket(QueueEntry $entry): void
    {
        $model = match ($entry->work_type) {
            'escalation' => \Modules\Escalations\Entities\Escalation::find($entry->work_id),
            'appointment' => \Modules\Appointment\Models\Appointment::find($entry->work_id),
            'outage' => \Modules\Outages\Models\Outage::find($entry->work_id),
            default => null,
        };

        $model?->update(['assigned_to' => $entry->assigned_to]);
    }
}
