<?php

namespace Modules\Outages\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Outages\Database\Factories\OutageTicketFactory;

class OutageTicket extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'outage_tickets';

    protected $fillable = [
        'outage_id',
        'ticket_number',
        'title',
        'description',
        'status',
        'priority',
        'reported_by',
        'assigned_to',
        'assigned_team_id',
        'impact',
        'urgency',
        'start_time',
        'end_time',
        'resolution',
        'resolution_notes',
        'sla_breached',
        'sla_breach_time',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'sla_breach_time' => 'datetime',
        'sla_breached' => 'boolean',
    ];

    protected static function newFactory()
    {
        return OutageTicketFactory::new();
    }

    /**
     * Get the outage that owns the ticket.
     */
    public function outage()
    {
        return $this->belongsTo(Outage::class);
    }

    /**
     * Get the user assigned to the ticket.
     */
    public function assignee()
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }

    /**
     * Get the user who reported the ticket.
     */
    public function reporter()
    {
        return $this->belongsTo(\App\Models\User::class, 'reported_by');
    }

    /**
     * Get the team assigned to the ticket.
     */
    public function assignedTeam()
    {
        return $this->belongsTo(\App\Models\Team::class, 'assigned_team_id');
    }

    /**
     * Get the reasons associated with the ticket.
     */
    public function reasons()
    {
        return $this->hasMany(OutageReason::class, 'ticket_id');
    }

    /**
     * Get the attachments associated with the ticket.
     */
    public function attachments()
    {
        return $this->hasMany(OutageAttachment::class, 'ticket_id');
    }

    /**
     * Get the progress updates for the ticket.
     */
    public function progress()
    {
        return $this->hasMany(OutageProgress::class, 'ticket_id')->latest();
    }

    /**
     * Scope a query to only include active tickets.
     */
    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'Resolved')
                    ->where('status', '!=', 'Closed');
    }

    /**
     * Scope a query to only include tickets assigned to a specific user.
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope a query to only include tickets assigned to a specific team.
     */
    public function scopeAssignedToTeam($query, $teamId)
    {
        return $query->where('assigned_team_id', $teamId);
    }

    /**
     * Check if the ticket is active.
     */
    public function isActive()
    {
        return !in_array($this->status, ['Resolved', 'Closed']);
    }

    /**
     * Check if the ticket is overdue.
     */
    public function isOverdue()
    {
        return $this->end_time && $this->end_time->isPast() && $this->isActive();
    }

    /**
     * Generate a new ticket number.
     */
    public static function generateTicketNumber()
    {
        $prefix = 'TICKET-' . date('Ymd');
        $lastTicket = static::where('ticket_number', 'like', $prefix . '%')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastTicket) {
            $number = (int) substr($lastTicket->ticket_number, -4) + 1;
        } else {
            $number = 1;
        }

        return $prefix . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the calculated priority based on impact and urgency.
     */
    public function calculatePriority()
    {
        $impact = strtolower($this->impact);
        $urgency = strtolower($this->urgency);

        if ($impact === 'high' && $urgency === 'high') {
            return 'Critical';
        } elseif (($impact === 'high' && $urgency === 'medium') || 
                 ($impact === 'medium' && $urgency === 'high')) {
            return 'High';
        } elseif (($impact === 'high' && $urgency === 'low') || 
                 ($impact === 'medium' && $urgency === 'medium') ||
                 ($impact === 'low' && $urgency === 'high')) {
            return 'Medium';
        } else {
            return 'Low';
        }
    }
}
