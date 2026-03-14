<?php

namespace Modules\Appointment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubAppointmentType extends Model
{
    protected $fillable = [
        'sub_type_name',
        'sub_type_description',
        'sub_type_status',
        'appointment_type_id',
        'created_by',
        'edited_by'
    ];

    protected $appends = ['status_badge'];

    public function appointmentType(): BelongsTo
    {
        return $this->belongsTo(AppointmentType::class, 'appointment_type_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'edited_by');
    }

    public function getStatusBadgeAttribute()
    {
        return $this->sub_type_status === 'Active' 
            ? '<span class="badge bg-label-success">Active</span>'
            : '<span class="badge bg-label-danger">Inactive</span>';
    }

    /**
     * Scope a query to only include active sub-types.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('sub_type_status', 'Active');
    }
}
