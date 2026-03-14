<?php

namespace Modules\Appointment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentStatusHistory extends Model
{
    protected $fillable = [
        'appointment_id',
        'previous_status',
        'new_status',
        'notes',
        'changed_by',
    ];

    /**
     * Get the appointment that owns this status history entry
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Get the user who changed the status
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'changed_by');
    }

    /**
     * Get the previous status record
     */
    public function previousStatusRecord(): BelongsTo
    {
        return $this->belongsTo(AppointmentStatus::class, 'previous_status', 'name');
    }

    /**
     * Get the new status record
     */
    public function newStatusRecord(): BelongsTo
    {
        return $this->belongsTo(AppointmentStatus::class, 'new_status', 'name');
    }
}
