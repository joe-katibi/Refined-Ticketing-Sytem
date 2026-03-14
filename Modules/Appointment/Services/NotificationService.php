<?php

namespace Modules\Appointment\Services;

use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentNotification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class NotificationService
{
    /**
     * Create a notification for an appointment.
     *
     * @param int $appointmentId
     * @param int $userId
     * @param string $message
     * @param string $type
     * @return AppointmentNotification
     */
    public function createNotification(int $appointmentId, int $userId, string $message, string $type): AppointmentNotification
    {
        // Create the notification
        $notification = AppointmentNotification::create([
            'appointment_id' => $appointmentId,
            'user_id' => $userId,
            'message' => $message,
            'type' => $type,
            'read' => false,
            'created_by' => Auth::id()
        ]);

        return $notification;
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
     * Create notifications for appointment creation.
     *
     * @param Appointment $appointment
     * @return void
     */
    public function notifyAppointmentCreated(Appointment $appointment): void
    {
        $creator = Auth::user();
        $ticketId = $appointment->appointment_ticket_id;
        
        // Create notification for the current user
        $message = "You created a new appointment ticket {$ticketId}";
        $this->createNotification($appointment->id, $creator->id, $message, 'create');
        
        // Also create notification for the assigned user if available and different from creator
        if (!empty($appointment->assigned_to) && $appointment->assigned_to != $creator->id) {
            $assignedUser = User::find($appointment->assigned_to);
            if ($assignedUser) {
                $message = "{$creator->name} created a new appointment ticket {$ticketId}";
                $this->createNotification($appointment->id, $assignedUser->id, $message, 'create');
            }
        }
        
        // Create toast notification with user info
        $this->setToastNotification('success', "{$creator->name} just created appointment ticket {$ticketId}");
    }

    /**
     * Create notifications for appointment update.
     *
     * @param Appointment $appointment
     * @param User $editor
     * @return void
     */
    public function notifyAppointmentUpdated(Appointment $appointment, User $editor): void
    {
        $ticketId = $appointment->appointment_ticket_id;
        
        // Always create a notification for the editor
        $message = "You updated appointment ticket {$ticketId}";
        $this->createNotification($appointment->id, $editor->id, $message, 'update');
        
        // Notify the creator if different from editor
        if (!empty($appointment->created_by) && $appointment->created_by != $editor->id) {
            $creator = User::find($appointment->created_by);
            if ($creator) {
                $message = "{$editor->name} updated appointment ticket {$ticketId}";
                $this->createNotification($appointment->id, $creator->id, $message, 'update');
            }
        }
        
        // Notify the assigned user if different from editor and creator
        if (!empty($appointment->assigned_to) && $appointment->assigned_to != $editor->id && 
            (!empty($appointment->created_by) && $appointment->assigned_to != $appointment->created_by)) {
            $assignedUser = User::find($appointment->assigned_to);
            if ($assignedUser) {
                $message = "{$editor->name} updated appointment ticket {$ticketId}";
                $this->createNotification($appointment->id, $assignedUser->id, $message, 'update');
            }
        }
        
        // Create toast notification with user info
        $this->setToastNotification('info', "{$editor->name} just updated appointment ticket {$ticketId}");
    }

    /**
     * Create notifications for appointment assignment.
     *
     * @param Appointment $appointment
     * @param User $assigner
     * @param User $assignee
     * @return void
     */
    public function notifyAppointmentAssigned(Appointment $appointment, User $assigner, User $assignee): void
    {
        $ticketId = $appointment->ticket_id;
        
        // Notify the assignee
        $message = "{$assigner->name} assigned appointment ticket {$ticketId} to you";
        $this->createNotification($appointment->id, $assignee->id, $message, 'assign');
        
        // Create toast notification
        $this->createToastNotification("Appointment ticket {$ticketId} assigned to {$assignee->name} successfully");
    }

    /**
     * Create notifications for appointment closure.
     *
     * @param Appointment $appointment
     * @param User $closer
     * @return void
     */
    public function notifyAppointmentClosed(Appointment $appointment, User $closer): void
    {
        $ticketId = $appointment->ticket_id;
        
        // Notify the creator if different from closer
        $creator = User::find($appointment->created_by);
        if ($creator && $creator->id !== $closer->id) {
            $message = "{$closer->name} closed appointment ticket {$ticketId}";
            $this->createNotification($appointment->id, $creator->id, $message, 'close');
        }
        
        // Notify the assigned user if different from closer and creator
        $assignedUser = User::find($appointment->assigned_to);
        if ($assignedUser && $assignedUser->id !== $closer->id && (!$creator || $assignedUser->id !== $creator->id)) {
            $message = "{$closer->name} closed appointment ticket {$ticketId}";
            $this->createNotification($appointment->id, $assignedUser->id, $message, 'close');
        }
        
        // Create toast notification
        $this->createToastNotification("Appointment ticket {$ticketId} closed successfully");
    }
}
