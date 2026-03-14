<?php

namespace App\Models;

use App\Traits\OptimizedQueries;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class OptimizedUser extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, OptimizedQueries;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'username', 
        'email',
        'password',
        'is_admin',
        'user_status',
        'department_id',
        'sub_department_id',
        'team_type_id',
        'sub_team_type_id',
        'phone',
        'created_by',
        'edited_by'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
    ];

    // Define default eager loading relationships
    protected $defaultEagerLoad = ['roles', 'department', 'teamType'];

    // Define searchable columns
    protected $searchableColumns = ['name', 'username', 'email'];

    // Define active status value
    protected $activeStatusValue = 'Active';

    /**
     * Optimized relationship definitions
     */
    public function department()
    {
        return $this->belongsTo(Department::class)->select(['id', 'department_name']);
    }

    public function subDepartment()
    {
        return $this->belongsTo(SubDepartment::class)->select(['id', 'sub_department_name']);
    }

    public function teamType()
    {
        return $this->belongsTo(TeamType::class)->select(['id', 'type_name']);
    }

    public function subTeamType()
    {
        return $this->belongsTo(SubTeamType::class)->select(['id', 'sub_type_name']);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }

    public function editedBy()
    {
        return $this->belongsTo(User::class, 'edited_by')->select(['id', 'name']);
    }

    /**
     * Optimized scopes
     */
    public function scopeWithBasicInfo($query)
    {
        return $query->select(['id', 'name', 'username', 'email', 'user_status', 'department_id']);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByTeamType($query, $teamTypeId)
    {
        return $query->where('team_type_id', $teamTypeId);
    }

    public function scopeAdmins($query)
    {
        return $query->where('is_admin', true);
    }

    /**
     * Get user's full display name
     */
    public function getFullNameAttribute()
    {
        return $this->name;
    }

    /**
     * Check if user is active
     */
    public function isActive()
    {
        return $this->user_status === 'Active';
    }

    /**
     * Get user's department name
     */
    public function getDepartmentNameAttribute()
    {
        return $this->department?->department_name ?? 'N/A';
    }
}
