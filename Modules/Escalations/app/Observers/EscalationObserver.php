<?php

namespace Modules\Escalations\App\Observers;

use Modules\Escalations\App\Models\Escalation;
use Modules\Escalations\App\Models\EscalationHistory;
use Illuminate\Support\Facades\Auth;

class EscalationObserver
{
    /**
     * Handle the Escalation "created" event.
     */
    public function created(Escalation $escalation): void
    {
        $ticket = $escalation->ticket;
        
        EscalationHistory::create([
            'escalation_id' => $escalation->id,
            'ticket_id' => $ticket->id ?? null,
            'status' => 'created',
            'action_by' => Auth::id(),
            'assigned_to' => $escalation->assigned_to,
            'sub_department_id' => $escalation->sub_department_id,
            'priority' => $escalation->priority ?? 'medium',
            'action_description' => 'Escalation created',
            'account_number' => $ticket->account_number ?? null,
            'sub_category_id' => $ticket->sub_category_id ?? null,
            'sub_department_id' => $ticket->sub_department_id ?? null,
        ]);
    }

    /**
     * Handle the Escalation "updated" event.
     */
    public function updated(Escalation $escalation): void
    {
        $changes = $escalation->getChanges();
        $original = $escalation->getOriginal();
        
        // Only create history if relevant fields changed
        $trackedFields = ['status', 'assigned_to', 'sub_department_id', 'priority'];
        $relevantChanges = array_intersect_key($changes, array_flip($trackedFields));
        
        if (empty($relevantChanges)) {
            return;
        }
        
        $latestHistory = $escalation->history()->first();
        
        $historyData = [
            'escalation_id' => $escalation->id,
            'action_by' => Auth::id(),
            'previous_history_id' => $latestHistory->id ?? null,
        ];
        
        // Set status and timestamps based on status change
        if (isset($changes['status'])) {
            $historyData['status'] = $changes['status'];
            
            switch ($changes['status']) {
                case 'assigned':
                    $historyData['assigned_at'] = now();
                    $historyData['assigned_to'] = $escalation->assigned_to;
                    $historyData['action_description'] = 'Assigned to ' . ($escalation->assignedTo->name ?? 'user');
                    break;
                    
                case 'in_progress':
                    $historyData['started_at'] = now();
                    $historyData['action_description'] = 'Work in progress';
                    break;
                    
                case 'resolved':
                    $historyData['resolved_at'] = now();
                    $historyData['action_description'] = 'Marked as resolved';
                    break;
                    
                case 'closed':
                    $historyData['closed_at'] = now();
                    $historyData['action_description'] = 'Escalation closed';
                    break;
                    
                case 'reopened':
                    $historyData['action_description'] = 'Escalation reopened';
                    break;
            }
        }
        
        // Update assignment if changed
        if (isset($changes['assigned_to'])) {
            $historyData['assigned_to'] = $escalation->assigned_to;
            $historyData['assigned_at'] = now();
            $historyData['action_description'] = isset($historyData['action_description']) 
                ? $historyData['action_description'] . ' and reassigned' 
                : 'Reassigned to ' . ($escalation->assignedTo->name ?? 'user');
        }
        
        // Update department if changed
        if (isset($changes['sub_department_id'])) {
            $historyData['sub_department_id'] = $escalation->sub_department_id;
            $historyData['action_description'] = isset($historyData['action_description']) 
                ? $historyData['action_description'] . ' and department changed' 
                : 'Department changed';
        }
        
        // Update priority if changed
        if (isset($changes['priority'])) {
            $historyData['priority'] = $escalation->priority;
            $historyData['action_description'] = isset($historyData['action_description'])
                ? $historyData['action_description'] . ' with priority change'
                : 'Priority changed to ' . $escalation->priority;
        }
        
        // Create the history record
        EscalationHistory::create($historyData);
        
        // Update time spent on previous status
        if ($latestHistory) {
            $latestHistory->calculateTimeSpent();
        }
    }
}
