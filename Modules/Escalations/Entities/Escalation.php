<?php

namespace Modules\Escalations\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Traits\OptimizedQueries;

class Escalation extends Model
{
    use OptimizedQueries;
    /**
     * Get the appointment associated with the escalation.
     */
    public function appointment()
    {
        return $this->belongsTo(\Modules\Appointment\Models\Appointment::class, 'appointment_id');
    }

    /**
     * Get the escalation list associated with the escalation.
     */
    public function escalationList()
    {
        return $this->hasOne(\Modules\Escalations\Entities\EscalationList::class, 'escalation_id');
    }

    protected $fillable = [
        'escalation_id',
        'ticket_id',
        'appointment_id',
        'appointment_type_id',
        'account_number',
        'category_id',
        'sub_category_id',
        'sub_department_id',
        'description',
        'priority',
        'status',
        'created_by',
        'assigned_to',
        'edited_by',
        'sla_deadline',
        'closed_at',
        'sla_breached',
        'sla_breach_notified',
        'closed_by',
        'olt_id',
        'slot_id',
        'region_id',
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

    protected $table = 'escalations';

    protected $casts = [
        'sla_deadline' => 'datetime',
        'closed_at' => 'datetime',
        'sla_breached' => 'boolean',
        'sla_breach_notified' => 'boolean',
    ];

    // Boot method to auto-generate escalation_id if not provided and set SLA deadline
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($escalation) {
            if (empty($escalation->escalation_id)) {
                $escalation->escalation_id = static::generateEscalationId();
            }

            // Set SLA deadline (5 minutes from creation)
            $escalation->sla_deadline = now()->addMinutes(5);
        });

        static::updating(function ($escalation) {
            // If status is changing to a closed status, set closed_at and check SLA
            if ($escalation->isDirty('status') && in_array($escalation->status, ['Scheduled-Closed', 'Escalated-Closed'])) {
                $escalation->closed_at = now();
                $escalation->closed_by = auth()->id();
                $escalation->sla_breached = now()->gt($escalation->sla_deadline);
            }
        });
    }

    // Static method to generate escalation ID
    public static function generateEscalationId()
    {
        $lastEscalation = static::orderBy('id', 'desc')->first();

        if (!$lastEscalation) {
            return 'ESC-1';
        }

        $lastNumber = (int) str_replace('ESC-', '', $lastEscalation->escalation_id);
        return 'ESC-' . ($lastNumber + 1);
    }

    /**
     * Get the user who created the escalation.
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who edited the escalation.
     */
    public function editor()
    {
        return $this->belongsTo(\App\Models\User::class, 'edited_by');
    }

    /**
     * Get the user who closed the escalation.
     */
    public function closer()
    {
        return $this->belongsTo(\App\Models\User::class, 'closed_by');
    }

    /**
     * Get the user assigned to the escalation.
     */
    public function assignedUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }

    /**
     * Get the department associated with the escalation.
     */
    public function department()
    {
        return $this->belongsTo(\App\Models\Department::class, 'department_id');
    }

    /**
     * Get the sub-department associated with the escalation.
     */
    public function subDepartment()
    {
        return $this->belongsTo(\App\Models\SubDepartment::class, 'sub_department_id');
    }

    /**
     * Get the category associated with the escalation.
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Get the subcategory associated with the escalation.
     */
    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class, 'sub_category_id');
    }

    /**
     * Get the notifications for this escalation.
     */
    public function notifications()
    {
        return $this->hasMany(EscalationNotification::class, 'escalation_id');
    }

    /**
     * Check if the escalation is closed.
     */
    public function isClosed()
    {
        return in_array($this->status, ['Scheduled-Closed', 'Escalated-Closed']);
    }

    /**
     * Check if the escalation is within SLA.
     */
    public function isWithinSla()
    {
        if (!$this->isClosed()) {
            return now()->lt($this->sla_deadline);
        }

        return !$this->sla_breached;
    }

    /**
     * Get the time remaining until SLA breach.
     */
    public function slaTimeRemaining()
    {
        if ($this->isClosed()) {
            return null;
        }

        if (now()->gt($this->sla_deadline)) {
            return 0;
        }

        return now()->diffInSeconds($this->sla_deadline);
    }

    /**
     * Get the formatted time remaining until SLA breach.
     */
    public function formattedSlaTimeRemaining()
    {
        $seconds = $this->slaTimeRemaining();

        if ($seconds === null) {
            return 'N/A';
        }

        if ($seconds === 0) {
            return 'SLA Breached';
        }

        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        return sprintf('%02d:%02d', $minutes, $remainingSeconds);
    }

    /**
     * Scope a query to only include escalations for a specific sub-department.
     */
    public function scopeBySubDepartment($query, $subDepartmentId)
    {
        return $query->where('sub_department_id', $subDepartmentId);
    }

    /**
     * Scope a query to only include escalations created today.
     */
    public function scopeCreatedToday($query)
    {
        return $query->whereDate('created_at', now()->toDateString());
    }

    /**
     * Scope a query to only include escalations created yesterday.
     */
    public function scopeCreatedYesterday($query)
    {
        return $query->whereDate('created_at', now()->subDay()->toDateString());
    }

    /**
     * Scope a query to only include closed escalations.
     */
    public function scopeClosed($query)
    {
        return $query->whereIn('status', ['Scheduled-Closed', 'Escalated-Closed']);
    }

    /**
     * Scope a query to only include open escalations.
     */
    public function scopeOpen($query)
    {
        return $query->whereNotIn('status', ['Scheduled-Closed', 'Escalated-Closed']);
    }

    /**
     * Scope a query to only include escalations closed within SLA.
     */
    public function scopeClosedWithinSla($query)
    {
        return $query->closed()->where('sla_breached', false);
    }

    /**
     * Scope a query to only include escalations closed outside SLA.
     */
    public function scopeClosedOutsideSla($query)
    {
        return $query->closed()->where('sla_breached', true);
    }
}
