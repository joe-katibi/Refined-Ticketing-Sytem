<?php

namespace Modules\Escalations\App\Services;

use Modules\Escalations\Models\Escalation;
use Modules\Escalations\Models\EscalationHistory;
use Illuminate\Support\Facades\Auth;

class EscalationHistoryService
{
    /**
     * Create a new history entry for an escalation.
     */
    public function createHistoryEntry(Escalation $escalation, array $data): EscalationHistory
    {
        $latestHistory = $escalation->history()->first();
        
        $historyData = array_merge([
            'escalation_id' => $escalation->id,
            'action_by' => Auth::id(),
            'previous_history_id' => $latestHistory->id ?? null,
            'status' => $escalation->status,
            'assigned_to' => $escalation->assigned_to,
            'sub_department_id' => $escalation->sub_department_id,
            'priority' => $escalation->priority,
        ], $data);
        
        // Update time spent on previous status
        if ($latestHistory) {
            $latestHistory->calculateTimeSpent();
        }
        
        return EscalationHistory::create($historyData);
    }
    
    /**
     * Create a history entry for a status change.
     */
    public function logStatusChange(Escalation $escalation, string $newStatus, ?string $notes = null): EscalationHistory
    {
        $data = [
            'status' => $newStatus,
            'action_description' => 'Status changed to ' . str_replace('_', ' ', $newStatus),
        ];
        
        // Set timestamps based on status
        switch ($newStatus) {
            case 'assigned':
                $data['assigned_at'] = now();
                break;
            case 'in_progress':
                $data['started_at'] = now();
                break;
            case 'resolved':
                $data['resolved_at'] = now();
                break;
            case 'closed':
                $data['closed_at'] = now();
                break;
        }
        
        if ($notes) {
            $data['internal_notes'] = $notes;
        }
        
        return $this->createHistoryEntry($escalation, $data);
    }
    
    /**
     * Log an assignment change.
     */
    public function logAssignment(Escalation $escalation, ?int $assignedTo, ?string $notes = null): EscalationHistory
    {
        $data = [
            'assigned_to' => $assignedTo,
            'assigned_at' => now(),
            'action_description' => $assignedTo 
                ? 'Assigned to user #' . $assignedTo 
                : 'Assignment cleared',
        ];
        
        if ($notes) {
            $data['internal_notes'] = $notes;
        }
        
        return $this->createHistoryEntry($escalation, $data);
    }
    
    /**
     * Log a department change.
     */
    public function logDepartmentChange(Escalation $escalation, ?int $departmentId, ?string $notes = null): EscalationHistory
    {
        $data = [
            'sub_department_id' => $departmentId,
            'action_description' => $departmentId 
                ? 'Transferred to department #' . $departmentId 
                : 'Department assignment cleared',
        ];
        
        if ($notes) {
            $data['internal_notes'] = $notes;
        }
        
        return $this->createHistoryEntry($escalation, $data);
    }
    
    /**
     * Log a priority change.
     */
    public function logPriorityChange(Escalation $escalation, string $priority, ?string $notes = null): EscalationHistory
    {
        $data = [
            'priority' => $priority,
            'action_description' => 'Priority changed to ' . $priority,
        ];
        
        if ($notes) {
            $data['internal_notes'] = $notes;
        }
        
        return $this->createHistoryEntry($escalation, $data);
    }
    
    /**
     * Log a custom action.
     */
    public function logCustomAction(Escalation $escalation, string $description, ?string $notes = null): EscalationHistory
    {
        $data = [
            'action_description' => $description,
        ];
        
        if ($notes) {
            $data['internal_notes'] = $notes;
        }
        
        return $this->createHistoryEntry($escalation, $data);
    }
}
