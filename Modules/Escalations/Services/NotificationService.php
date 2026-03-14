<?php

namespace Modules\Escalations\Services;

use Modules\Escalations\Entities\Escalation as EntitiesEscalation;
use Modules\Escalations\App\Models\Escalation as ModelsEscalation;
use Modules\Escalations\Entities\EscalationNotification;
use App\Models\User;
use Illuminate\Support\Facades\Session;

class NotificationService
{
    /**
     * Create a notification for escalation creation.
     *
     * @param EntitiesEscalation|ModelsEscalation $escalation
     * @return void
     */
    public function notifyCreation($escalation)
    {
        // Get creator user information
        $creator = User::find($escalation->created_by);
        $creatorName = $creator ? $creator->name : 'Unknown user';
        
        // Create toast notification message
        $toastMessage = "{$creatorName} just created a {$this->getCategoryName($escalation)} ticket {$escalation->ticket_id}";
        
        // Set flash message for toast notification
        $this->setToastNotification('success', $toastMessage);
        
        // Notify the creator
        if ($escalation->created_by) {
            $this->createNotification(
                $escalation->created_by,
                $escalation->id,
                'create',
                "You created escalation {$escalation->ticket_id}."
            );
        }

        // Notify the assigned user if different from creator
        if ($escalation->assigned_to && $escalation->assigned_to != $escalation->created_by) {
            $this->createNotification(
                $escalation->assigned_to,
                $escalation->id,
                'assign',
                "{$creatorName} assigned escalation {$escalation->ticket_id} to you."
            );
        }
    }

    /**
     * Create a notification for escalation update.
     *
     * @param EntitiesEscalation|ModelsEscalation $escalation
     * @param array $changes
     * @return void
     */
    public function notifyUpdate($escalation, array $changes = [])
    {
        // Get editor user information
        $editor = User::find($escalation->edited_by);
        $editorName = $editor ? $editor->name : 'Unknown user';
        
        // Create toast notification message
        $toastMessage = "{$editorName} just updated {$escalation->ticket_id}";
        
        // Set flash message for toast notification
        $this->setToastNotification('info', $toastMessage);
        
        // Notify the creator if different from editor
        if ($escalation->created_by && $escalation->created_by != $escalation->edited_by) {
            $this->createNotification(
                $escalation->created_by,
                $escalation->id,
                'update',
                "{$editorName} updated escalation {$escalation->ticket_id}."
            );
        }

        // Notify the assigned user if different from editor
        if ($escalation->assigned_to && $escalation->assigned_to != $escalation->edited_by) {
            $this->createNotification(
                $escalation->assigned_to,
                $escalation->id,
                'update',
                "{$editorName} updated escalation {$escalation->ticket_id} assigned to you."
            );
        }

        // If status changed to closed, notify relevant users
        if (isset($changes['status']) && in_array($escalation->status, ['Scheduled-Closed', 'Escalated-Closed'])) {
            $this->notifyClosing($escalation);
        }
    }

    /**
     * Create a notification for escalation closing.
     *
     * @param EntitiesEscalation|ModelsEscalation $escalation
     * @return void
     */
    public function notifyClosing($escalation)
    {
        $slaStatus = $escalation->sla_breached ? 'outside SLA' : 'within SLA';
        
        // Get closer user information
        $closer = User::find($escalation->closed_by);
        $closerName = $closer ? $closer->name : 'Unknown user';
        
        // Create toast notification message
        $toastMessage = "{$closerName} just closed ticket {$escalation->ticket_id} {$slaStatus}";
        
        // Set flash message for toast notification
        $this->setToastNotification('warning', $toastMessage);
        
        // Notify the creator if different from closer
        if ($escalation->created_by && $escalation->created_by != $escalation->closed_by) {
            $this->createNotification(
                $escalation->created_by,
                $escalation->id,
                'close',
                "{$closerName} closed escalation {$escalation->ticket_id} {$slaStatus}."
            );
        }

        // Notify the assigned user if different from closer
        if ($escalation->assigned_to && $escalation->assigned_to != $escalation->closed_by) {
            $this->createNotification(
                $escalation->assigned_to,
                $escalation->id,
                'close',
                "{$closerName} closed escalation {$escalation->ticket_id} assigned to you {$slaStatus}."
            );
        }
    }

    /**
     * Create a notification for SLA breach.
     *
     * @param EntitiesEscalation|ModelsEscalation $escalation
     * @return void
     */
    public function notifySlaBreached($escalation)
    {
        // Create toast notification message
        $toastMessage = "SLA BREACH: Ticket {$escalation->ticket_id} has exceeded its SLA";
        
        // Set flash message for toast notification
        $this->setToastNotification('error', $toastMessage);
        
        // Notify the assigned user
        if ($escalation->assigned_to) {
            $this->createNotification(
                $escalation->assigned_to,
                $escalation->id,
                'sla_breach',
                "SLA for Escalation {$escalation->ticket_id} has been breached. Immediate action required."
            );
        }

        // Notify the creator if different from assigned user
        if ($escalation->created_by && $escalation->created_by != $escalation->assigned_to) {
            $this->createNotification(
                $escalation->created_by,
                $escalation->id,
                'sla_breach',
                "SLA for Escalation {$escalation->ticket_id} has been breached."
            );
        }
    }
    
    /**
     * Create a notification for SLA warning (approaching deadline).
     *
     * @param EntitiesEscalation|ModelsEscalation $escalation
     * @param int $minutesRemaining
     * @return void
     */
    public function notifySlaWarning($escalation, int $minutesRemaining)
    {
        $timeRemaining = floor($minutesRemaining / 60) . ':' . str_pad($minutesRemaining % 60, 2, '0', STR_PAD_LEFT);
        
        // Create toast notification message
        $toastMessage = "WARNING: SLA for ticket {$escalation->ticket_id} will breach in {$timeRemaining}";
        
        // Set flash message for toast notification
        $this->setToastNotification('warning', $toastMessage);
        
        // Notify the assigned user
        if ($escalation->assigned_to) {
            $this->createNotification(
                $escalation->assigned_to,
                $escalation->id,
                'sla_warning',
                "WARNING: SLA for Escalation {$escalation->ticket_id} will breach in {$timeRemaining}. Urgent action required."
            );
        }

        // Notify the creator if different from assigned user
        if ($escalation->created_by && $escalation->created_by != $escalation->assigned_to) {
            $this->createNotification(
                $escalation->created_by,
                $escalation->id,
                'sla_warning',
                "WARNING: SLA for Escalation {$escalation->ticket_id} will breach in {$timeRemaining}."
            );
        }
    }

    /**
     * Create a notification record.
     *
     * @param int $userId
     * @param int $escalationId
     * @param string $message
     * @param string $type
     * @return EscalationNotification
     */
    public function createNotification($userId, $escalationId, $message, $type)
    {
        // Check if this is an EscalationList ID rather than an Escalation ID
        if (\Modules\Escalations\Entities\EscalationList::find($escalationId) && !\Modules\Escalations\Entities\Escalation::find($escalationId)) {
            // This is likely an EscalationList ID - find the corresponding Escalation
            $escalationList = \Modules\Escalations\Entities\EscalationList::find($escalationId);
            if ($escalationList) {
                $escalation = \Modules\Escalations\Entities\Escalation::where('ticket_id', $escalationList->ticket_id)->first();
                if ($escalation) {
                    $escalationId = $escalation->id; // Use the correct Escalation ID
                }
            }
        }
        
        return EscalationNotification::create([
            'user_id' => $userId,
            'escalation_id' => $escalationId,
            'type' => $type,
            'message' => $message,
            'read' => false,
        ]);
    }
    
    /**
     * Create a notification record for an escalation list item.
     * This method ensures the correct Escalation ID is used for the foreign key.
     *
     * @param int $userId
     * @param int $escalationListId
     * @param string $message
     * @param string $type
     * @return EscalationNotification|null
     */
    public function createEscalationListNotification($userId, $escalationListId, $message, $type)
    {
        // Find the EscalationList record
        $escalationList = \Modules\Escalations\Entities\EscalationList::find($escalationListId);
        
        if (!$escalationList) {
            // Log error and return null if escalation list not found
            \Log::error('EscalationList not found', ['id' => $escalationListId]);
            return null;
        }
        
        // Find the associated Escalation record
        $escalation = \Modules\Escalations\Entities\Escalation::where('ticket_id', $escalationList->ticket_id)->first();
        
        if (!$escalation) {
            // Log error and return null if escalation not found
            \Log::error('Escalation not found for ticket_id', ['ticket_id' => $escalationList->ticket_id]);
            return null;
        }
        
        // Create notification with the correct Escalation ID
        return $this->createNotification($userId, $escalation->id, $message, $type);
    }
    
    /**
     * Set toast notification in session.
     *
     * @param string $type success|info|warning|error
     * @param string $message
     * @return void
     */
    public function setToastNotification($type, $message)
    {
        Session::flash('toast_type', $type);
        Session::flash('toast_message', $message);
        Session::flash('toast_show', true);
    }
    
    /**
     * Get category name for the escalation.
     *
     * @param EntitiesEscalation|ModelsEscalation $escalation
     * @return string
     */
    private function getCategoryName($escalation)
    {
        // Try to get category name if available
        if (method_exists($escalation, 'category') && $escalation->category) {
            return strtolower($escalation->category->name);
        }
        
        return 'escalation';
    }
}
