<?php

namespace Modules\Outages\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Outages\Entities\OutageTicket;

class OutageTicketNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $ticket;
    protected $type;
    protected $data;

    /**
     * Create a new notification instance.
     */
    public function __construct(OutageTicket $ticket, string $type, array $data = [])
    {
        $this->ticket = $ticket;
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $mailMessage = new MailMessage();

        switch ($this->type) {
            case 'created':
                return $mailMessage
                    ->subject('New Outage Ticket Created: ' . $this->ticket->ticket_number)
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line('A new outage ticket has been created.')
                    ->line('**Ticket Details:**')
                    ->line('Ticket Number: ' . $this->ticket->ticket_number)
                    ->line('Title: ' . $this->ticket->title)
                    ->line('Priority: ' . $this->ticket->priority)
                    ->line('Impact: ' . $this->ticket->impact)
                    ->line('Status: ' . $this->ticket->status)
                    ->line('Related Outage: ' . $this->ticket->outage->ticket_number)
                    ->action('View Ticket', route('outage-tickets.show', $this->ticket))
                    ->line('Please review and take appropriate action.');

            case 'assigned':
                return $mailMessage
                    ->subject('Outage Ticket Assigned: ' . $this->ticket->ticket_number)
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line('An outage ticket has been assigned to you.')
                    ->line('**Ticket Details:**')
                    ->line('Ticket Number: ' . $this->ticket->ticket_number)
                    ->line('Title: ' . $this->ticket->title)
                    ->line('Priority: ' . $this->ticket->priority)
                    ->line('Assigned by: ' . ($this->data['assigned_by'] ?? 'System'))
                    ->line('Related Outage: ' . $this->ticket->outage->ticket_number)
                    ->action('View Ticket', route('outage-tickets.show', $this->ticket))
                    ->line('Please review and begin work on this ticket.');

            case 'status_changed':
                return $mailMessage
                    ->subject('Ticket Status Updated: ' . $this->ticket->ticket_number)
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line('The status of an outage ticket has been updated.')
                    ->line('**Ticket Details:**')
                    ->line('Ticket Number: ' . $this->ticket->ticket_number)
                    ->line('Title: ' . $this->ticket->title)
                    ->line('Previous Status: ' . ($this->data['old_status'] ?? 'Unknown'))
                    ->line('New Status: ' . $this->ticket->status)
                    ->line('Updated by: ' . ($this->data['updated_by'] ?? 'System'))
                    ->action('View Ticket', route('outage-tickets.show', $this->ticket))
                    ->line('Thank you for staying informed.');

            case 'progress_update':
                return $mailMessage
                    ->subject('Progress Update: ' . $this->ticket->ticket_number)
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line('A progress update has been added to an outage ticket.')
                    ->line('**Ticket Details:**')
                    ->line('Ticket Number: ' . $this->ticket->ticket_number)
                    ->line('Title: ' . $this->ticket->title)
                    ->line('Status: ' . $this->ticket->status)
                    ->line('Updated by: ' . ($this->data['updated_by'] ?? 'System'))
                    ->line('**Progress Notes:**')
                    ->line($this->data['notes'] ?? 'No notes provided')
                    ->action('View Ticket', route('outage-tickets.show', $this->ticket))
                    ->line('Please review the latest progress update.');

            case 'sla_breach':
                return $mailMessage
                    ->subject('🚨 TICKET SLA BREACH: ' . $this->ticket->ticket_number)
                    ->greeting('URGENT: Ticket SLA Breach Alert')
                    ->line('An outage ticket has breached its SLA and requires immediate attention.')
                    ->line('**Critical Ticket Details:**')
                    ->line('Ticket Number: ' . $this->ticket->ticket_number)
                    ->line('Title: ' . $this->ticket->title)
                    ->line('Priority: ' . $this->ticket->priority)
                    ->line('Impact: ' . $this->ticket->impact)
                    ->line('Start Time: ' . $this->ticket->start_time->format('M d, Y H:i A'))
                    ->line('Breach Time: ' . ($this->ticket->sla_breach_time ? $this->ticket->sla_breach_time->format('M d, Y H:i A') : 'Now'))
                    ->line('Duration: ' . $this->ticket->start_time->diffForHumans())
                    ->line('Related Outage: ' . $this->ticket->outage->ticket_number)
                    ->action('View Ticket Immediately', route('outage-tickets.show', $this->ticket))
                    ->line('**This requires immediate escalation and resolution.**')
                    ->salutation('Outage Management System');

            case 'resolved':
                return $mailMessage
                    ->subject('Ticket Resolved: ' . $this->ticket->ticket_number)
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line('An outage ticket has been resolved.')
                    ->line('**Ticket Details:**')
                    ->line('Ticket Number: ' . $this->ticket->ticket_number)
                    ->line('Title: ' . $this->ticket->title)
                    ->line('Resolution: ' . ($this->ticket->resolution ?? 'Not specified'))
                    ->line('Resolved by: ' . ($this->data['resolved_by'] ?? 'System'))
                    ->line('Duration: ' . $this->ticket->start_time->diffForHumans($this->ticket->end_time, true))
                    ->action('View Ticket', route('outage-tickets.show', $this->ticket))
                    ->line('Thank you for your attention to this matter.');

            case 'escalated':
                return $mailMessage
                    ->subject('Ticket Escalated: ' . $this->ticket->ticket_number)
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line('An outage ticket has been escalated due to SLA breach or priority.')
                    ->line('**Ticket Details:**')
                    ->line('Ticket Number: ' . $this->ticket->ticket_number)
                    ->line('Title: ' . $this->ticket->title)
                    ->line('Priority: ' . $this->ticket->priority)
                    ->line('Escalated by: ' . ($this->data['escalated_by'] ?? 'System'))
                    ->line('Escalation Reason: ' . ($this->data['escalation_reason'] ?? 'SLA Breach'))
                    ->action('View Ticket', route('outage-tickets.show', $this->ticket))
                    ->line('Please take immediate action on this escalated ticket.');

            default:
                return $mailMessage
                    ->subject('Ticket Update: ' . $this->ticket->ticket_number)
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line('There has been an update to an outage ticket.')
                    ->action('View Ticket', route('outage-tickets.show', $this->ticket));
        }
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title' => $this->ticket->title,
            'type' => $this->type,
            'priority' => $this->ticket->priority,
            'status' => $this->ticket->status,
            'outage_id' => $this->ticket->outage_id,
            'outage_number' => $this->ticket->outage->ticket_number,
            'data' => $this->data,
            'url' => route('outage-tickets.show', $this->ticket),
        ];
    }

    /**
     * Get the notification's database type.
     */
    public function databaseType($notifiable): string
    {
        return 'outage_ticket_' . $this->type;
    }
}
