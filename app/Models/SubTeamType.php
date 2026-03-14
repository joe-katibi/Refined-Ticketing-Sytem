<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubTeamType extends Model
{
    protected $fillable = [
        'sub_type_name',
        'sub_type_description',
        'sub_type_status',
        'team_type_id',
        'department_id',
        'sub_department_id',
        'created_by',
        'edited_by',
    ];

    protected $casts = [
        'sub_type_status' => 'string',
    ];

    /**
     * Get the team type that owns the sub-team type.
     */
    public function teamType(): BelongsTo
    {
        return $this->belongsTo(TeamType::class);
    }

    /**
     * Get the user who created this sub-team type.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last edited this sub-team type.
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    /**
     * Get the department that owns this sub-team type.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * Get the sub department that owns this sub-team type.
     */
    public function subDepartment(): BelongsTo
    {
        return $this->belongsTo(SubDepartment::class, 'sub_department_id');
    }

    /**
     * Get the status badge HTML.
     */
    public function getStatusBadgeAttribute(): string
    {
        return $this->sub_type_status === 'Active' 
            ? '<span class="badge bg-label-success">Active</span>'
            : '<span class="badge bg-label-danger">Inactive</span>';
    }
}
