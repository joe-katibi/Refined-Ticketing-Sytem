<?php

namespace Modules\Escalations\App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\SubDepartment;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EscalationHistory extends Model
{
    use HasFactory;

    protected $table = 'escalation_histories';

    // Status constants for easy reference
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_REOPENED = 'reopened';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'escalation_id',
        'ticket_id',
        'status',
        'action_by',
        'assigned_to',
        'department_id',
        'sub_department_id',
        'priority',
        'assigned_at',
        'started_at',
        'resolved_at',
        'closed_at',
        'time_spent_seconds',
        'previous_history_id',
        'action_description',
        'internal_notes',
        'sla_deadline',
        'sla_breached',
        'time_spent_minutes',
        'account_number',
        'sub_category_id',
        'category_id',
        'description',
        'olt_id',
        'slot_id',
        'appointment_type_id',
        'support_date',
        'support_time',
        'support_address',
        'support_notes',
        'shifting_date',
        'shifting_time',
        'shifting_address',
        'shifting_notes',
        'installation_date',
        'installation_time',
        'installation_address',
        'installation_notes',
        'wifi_extender_date',
        'wifi_extender_time',
        'wifi_extender_address',
        'wifi_extender_notes',
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
     * Get the ticket associated with this history entry.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(\Modules\Escalations\Entities\EscalationList::class, 'ticket_id');
    }

    /**
     * Get the subcategory associated with this history entry.
     */
    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(\Modules\Escalations\Entities\SubCategory::class, 'sub_category_id');
    }

    /**
     * Get the department associated with this history entry.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Department::class, 'department_id');
    }

    /**
     * Get the sub-department associated with this history entry.
     */
    public function subDepartment(): BelongsTo
    {
        return $this->belongsTo(\App\Models\SubDepartment::class, 'sub_department_id');
    }

    /**
     * Get the escalation that this history belongs to.
     */
    public function escalation(): BelongsTo
    {
        return $this->belongsTo(Escalation::class);
    }

    /**
     * Get the user who performed the action.
     */
    public function actionBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_by');
    }

    /**
     * Get the user this was assigned to.
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the previous history entry in the chain.
     */
    public function previousHistory(): BelongsTo
    {
        return $this->belongsTo(EscalationHistory::class, 'previous_history_id');
    }

    /**
     * Get the next history entry in the chain.
     */
    public function nextHistory(): HasMany
    {
        return $this->hasMany(EscalationHistory::class, 'previous_history_id');
    }

    /**
     * Scope a query to only include the latest history entry for each escalation.
     */
    public function scopeLatestForEscalation($query)
    {
        return $query->whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                  ->from('escalation_histories as eh2')
                  ->whereColumn('eh2.escalation_id', 'escalation_histories.escalation_id')
                  ->whereColumn('eh2.created_at', '>', 'escalation_histories.created_at');
        });
    }

    /**
     * Calculate the time spent in this status.
     */
    public function calculateTimeSpent(): void
    {
        $endTime = $this->resolved_at ?? $this->closed_at ?? now();
        $startTime = $this->started_at ?? $this->assigned_at ?? $this->created_at;

        $this->time_spent_seconds = $endTime->diffInSeconds($startTime);
        $this->save();
    }

    /**
     * Get the formatted time spent on this history entry.
     *
     * @return string
     */
    public function getFormattedTimeSpent(): string
    {
        // If time_spent_minutes is set, use it directly
        if ($this->time_spent_minutes !== null) {
            $hours = floor($this->time_spent_minutes / 60);
            $minutes = $this->time_spent_minutes % 60;

            $parts = [];
            if ($hours > 0) {
                $parts[] = $hours . 'h';
            }
            $parts[] = $minutes . 'm';

            return implode(' ', $parts);
        }

        // Fallback to time_spent_seconds if time_spent_minutes is not set
        if (!$this->time_spent_seconds) {
            return 'N/A';
        }

        $hours = floor($this->time_spent_seconds / 3600);
        $minutes = floor(($this->time_spent_seconds % 3600) / 60);
        $seconds = $this->time_spent_seconds % 60;

        $parts = [];
        if ($hours > 0) {
            $parts[] = $hours . 'h';
        }
        if ($minutes > 0 || $hours > 0) {
            $parts[] = $minutes . 'm';
        }
        if ($seconds > 0 && $hours === 0) {
            $parts[] = $seconds . 's';
        }

        return implode(' ', $parts) ?: '0m';
    }

    /**
     * Get the appropriate badge class for the status.
     *
     * @return string
     */
    public function getStatusBadgeClass(): string
    {
        $status = strtolower($this->status);

        switch ($status) {
            case self::STATUS_ASSIGNED:
                return 'primary';
            case self::STATUS_IN_PROGRESS:
                return 'warning';
            case self::STATUS_RESOLVED:
                return 'success';
            case self::STATUS_CLOSED:
                return 'secondary';
            case self::STATUS_REOPENED:
                return 'info';
            default:
                return 'light';
        }
    }

    /**
     * Get the appropriate timeline badge class.
     *
     * @return string
     */
    public function getTimelineBadgeClass(): string
    {
        if ($this->status) {
            return $this->getStatusBadgeClass();
        }

        // Default to primary if no status is set
        return 'primary';
    }

    /**
     * Get the appropriate icon for the timeline item.
     *
     * @return string
     */
    public function getTimelineIcon(): string
    {
        if ($this->status) {
            switch (strtolower($this->status)) {
                case self::STATUS_ASSIGNED:
                    return 'fa-user-tag';
                case self::STATUS_IN_PROGRESS:
                    return 'fa-spinner fa-spin';
                case self::STATUS_RESOLVED:
                    return 'fa-check-circle';
                case self::STATUS_CLOSED:
                    return 'fa-lock';
                case self::STATUS_REOPENED:
                    return 'fa-redo';
            }
        }

        // Default icon
        return 'fa-info-circle';
    }

    /**
     * Get the time spent in minutes.
     *
     * @return int
     */
    public function getTimeSpentMinutesAttribute(): int
    {
        // Return time_spent_minutes if it's set and not null
        if (array_key_exists('time_spent_minutes', $this->attributes) && $this->attributes['time_spent_minutes'] !== null) {
            return (int) $this->attributes['time_spent_minutes'];
        }

        // Fall back to calculating from time_spent_seconds if available
        if ($this->time_spent_seconds) {
            return (int) ceil($this->time_spent_seconds / 60);
        }

        // Default to 0 if neither is available
        return 0;
    }
}
