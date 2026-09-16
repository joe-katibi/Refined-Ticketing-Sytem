<?php

namespace Modules\Appointment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentType extends Model
{
    protected $fillable = [
        'type_name',
        'code_prefix',
        'type_description',
        'type_status',
        'created_by',
        'edited_by'
    ];

    protected $appends = ['status_badge'];

    public function subTypes(): HasMany
    {
        return $this->hasMany(SubAppointmentType::class, 'appointment_type_id');
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
        return $this->type_status === 'Active' 
            ? '<span class="badge bg-label-success">Active</span>'
            : '<span class="badge bg-label-danger">Inactive</span>';
    }

    /**
     * Scope a query to only include active types.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('type_status', 'Active');
    }
}
