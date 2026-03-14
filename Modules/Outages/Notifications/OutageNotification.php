<?php

namespace Modules\Outages\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Outages\Entities\Outage;

class OutageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $outage;
    protected $type;
    protected $data;

    /**
     * Create a new notification instance.
     */
    public function __construct(Outage $outage, string $type, array $data = [])
    {
        $this->outage = $outage;
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
                    ->subject('New Outage Created: ' . $this->outage->ticket_number)
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line('A new outage has been created and requires your attention.')
                    ->line('**Outage Details:**')
                    ->line('Ticket Number: ' . $this->outage->ticket_number)
                    ->line('Title: ' . $this->outage->title)
                    ->line('Priority: ' . $this->outage->priority)
                    ->line('Impact: ' . $this->outage->impact)
                    ->line('Status: ' . $this->outage->status)
                    ->line('Start Time: ' . $this->outage->start_time->format('M d, Y H:i A'))
                    ->action('View Outage', route('outages.show', $this->outage))
                    ->line('Please take appropriate action as soon as possible.');

            case 'assigned':
                return $mailMessage
                    ->subject('Outage Assigned: ' . $this->outage->ticket_number)
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line('An outage has been assigned to you.')
                    ->line('**Outage Details:**')
                    ->line('Ticket Number: ' . $this->outage->ticket_number)
                    ->line('Title: ' . $this->outage->title)
                    ->line('Priority: ' . $this->outage->priority)
                    ->line('Assigned by: ' . ($this->data['assigned_by'] ?? 'System'))
                    ->action('View Outage', route('outages.show', $this->outage))
                    ->line('Please review and take necessary action.');

            case 'status_changed':
                return $mailMessage
                    ->subject('Outage Status Updated: ' . $this->outage->ticket_number)
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line('The status of an outage has been updated.')
                    ->line('**Outage Details:**')
                    ->line('Ticket Number: ' . $this->outage->ticket_number)
                    ->line('Title: ' . $this->outage->title)
                    ->line('Previous Status: ' . ($this->data['old_status'] ?? 'Unknown'))
                    ->line('New Status: ' . $this->outage->status)
                    ->line('Updated by: ' . ($this->data['updated_by'] ?? 'System'))
                    ->action('View Outage', route('outages.show', $this->outage))
                    ->line('Thank you for staying informed.');

            case 'sla_breach':
                return $mailMessage
                    ->subject('🚨 SLA BREACH ALERT: ' . $this->outage->ticket_number)
                    ->greeting('URGENT: SLA Breach Alert')
                    ->line('An outage has breached its SLA and requires immediate attention.')
                    ->line('**Critical Outage Details:**')
                    ->line('Ticket Number: ' . $this->outage->ticket_number)
                    ->line('Title: ' . $this->outage->title)
                    ->line('Priority: ' . $this->outage->priority)
                    ->line('Impact: ' . $this->outage->impact)
                    ->line('Start Time: ' . $this->outage->start_time->format('M d, Y H:i A'))
                    ->line('Breach Time: ' . ($this->outage->sla_breach_time ? $this->outage->sla_breach_time->format('M d, Y H:i A') : 'Now'))
                    ->line('Duration: ' . $this->outage->start_time->diffForHumans())
                    ->action('View Outage Immediately', route('outages.show', $this->outage))
                    ->line('**This requires immediate escalation and resolution.**')
                    ->salutation('Outage Management System');

            case 'resolved':
                return $mailMessage
                    ->subject('Outage Resolved: ' . $this->outage->ticket_number)
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line('An outage has been resolved.')
                    ->line('**Outage Details:**')
                    ->line('Ticket Number: ' . $this->outage->ticket_number)
                    ->line('Title: ' . $this->outage->title)
                    ->line('Resolution: ' . ($this->outage->resolution ?? 'Not specified'))
                    ->line('Resolved by: ' . ($this->data['resolved_by'] ?? 'System'))
                    ->line('Duration: ' . $this->outage->start_time->diffForHumans($this->outage->end_time, true))
                    ->action('View Outage', route('outages.show', $this->outage))
                    ->line('Thank you for your attention to this matter.');

            default:
                return $mailMessage
                    ->subject('Outage Update: ' . $this->outage->ticket_number)
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line('There has been an update to an outage.')
                    ->action('View Outage', route('outages.show', $this->outage));
        }
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'outage_id' => $this->outage->id,
            'ticket_number' => $this->outage->ticket_number,
            'title' => $this->outage->title,
            'type' => $this->type,
            'priority' => $this->outage->priority,
            'status' => $this->outage->status,
            'data' => $this->data,
            'url' => route('outages.show', $this->outage),
        ];
    }

    /**
     * Get the notification's database type.
     */
    public function databaseType($notifiable): string
    {
        return 'outage_' . $this->type;
    }
}
