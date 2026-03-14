<?php

namespace Modules\Appointment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Appointment\Models\Appointment;
use App\Models\User;

class AppointmentHistory extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'appointment_histories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'appointment_id',
        'escalation_ticket_id',
        'ticket_id',
        'status',
        'action_by',
        'assigned_to',
        'sub_department_id',
        'appointment_id',
        'appointment_type_id',
        'sub_department_id',
        'category_id',
        'sub_category_id',
        'olt_id',
        'description_notes',
        'escalation_type',
        'status',
        'scheduled_date',
        'scheduled_time',
        'completed_date',
        'completed_time',
        'assigned_team_id',
        'escalated_team_id',
        'team_type_id',
        'sub_team_type_id',
        'closed_by',
        'notes_created',
        'notes_closed',
        'closing_reason',
        'escalation_reason',
        'escalation_notes',
        'appointment_type',
        'appointment_status',
        'appointment_location',
        'appointment_venue',
        'created_by',
        'edited_by',
        'closed_at',
        'escalated_at',
        'priority',
        'assigned_at',
        'started_at',
        'resolved_at',
        'closed_at',
        'time_spent_seconds',
        'time_spent_minutes',
        'previous_history_id',
        'action_description',
        'internal_notes',
        'sla_deadline',
        'sla_breached',
        'account_number',
        'sub_category_id',
        'category_id',
        'description',
        'comment',
        'rescheduled_date',
        'rescheduled_time',
        'reschedule_reason',
        'cancelled_reason',
        'final_reason_id',
        'changes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'sla_deadline' => 'datetime',
        'sla_breached' => 'boolean',
    ];

    /**
     * Get the appointment that this history belongs to.
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    /**
     * Get the user who performed the action.
     */
    public function actionBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_by');
    }

    /**
     * Get the user who performed the action.
     */
    public function actionUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'action_by');
    }

    /**
     * Get the user assigned to this history entry.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }

    /**
     * Get the sub-department associated with this history entry.
     */
    public function subDepartment(): BelongsTo
    {
        return $this->belongsTo(\App\Models\SubDepartment::class, 'sub_department_id');
    }
    
    /**
     * Get the formatted time spent for this history entry.
     *
     * @return string
     */
    public function getFormattedTimeSpent(): string
    {
        if ($this->time_spent_minutes) {
            $hours = floor($this->time_spent_minutes / 60);
            $minutes = $this->time_spent_minutes % 60;
            
            if ($hours > 0) {
                return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ' . $minutes . ' minute' . ($minutes != 1 ? 's' : '');
            } else {
                return $minutes . ' minute' . ($minutes != 1 ? 's' : '');
            }
        }
        
        return 'N/A';
    }
    
    /**
     * Get the timeline icon class based on action description.
     *
     * @return string
     */
    public function getTimelineIcon(): string
    {
        $actionDesc = strtolower($this->action_description ?? '');
        
        if (str_contains($actionDesc, 'created')) {
            return 'fa-plus-circle';
        } elseif (str_contains($actionDesc, 'updated') || str_contains($actionDesc, 'edit')) {
            return 'fa-edit';
        } elseif (str_contains($actionDesc, 'completed')) {
            return 'fa-check-circle';
        } elseif (str_contains($actionDesc, 'cancelled')) {
            return 'fa-times-circle';
        } elseif (str_contains($actionDesc, 'rescheduled')) {
            return 'fa-calendar-alt';
        } else {
            return 'fa-exclamation-circle';
        }
    }
    
    /**
     * Get the status badge class.
     *
     * @return string
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'completed' => 'success',
            'in_progress' => 'primary',
            'cancelled' => 'danger',
            'rescheduled' => 'warning',
            default => 'secondary'
        };
    }
}
