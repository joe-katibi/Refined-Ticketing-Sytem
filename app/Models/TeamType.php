<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamType extends Model
{
    protected $fillable = [
        'type_name',
        'description',
        'status',
        'department_id',
        'sub_department_id',
        'created_by',
        'edited_by',
    ];

    protected $appends = ['status_badge'];

    /**
     * Get all sub-team types for this team type.
     */
    public function subTypes(): HasMany
    {
        return $this->hasMany(SubTeamType::class, 'team_type_id');
    }

    /**
     * Get all teams for this team type.
     */
    public function teams(): HasMany
    {
        return $this->hasMany(Team::class, 'team_type_id');
    }

    /**
     * Get the user who created this team type.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last edited this team type.
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    /**
     * Get the department that owns this team type.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * Get the sub department that owns this team type.
     */
    public function subDepartment(): BelongsTo
    {
        return $this->belongsTo(SubDepartment::class, 'sub_department_id');
    }

    /**
     * Scope a query to only include active team types.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'Active');
    }

    /**
     * Get the status badge HTML.
     */
    public function getStatusBadgeAttribute(): string
    {
        return $this->status === 'Active'
            ? '<span class="badge bg-label-success">Active</span>'
            : '<span class="badge bg-label-danger">Inactive</span>';
    }
}
