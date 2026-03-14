<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable=[
        'department_name',
        'description',
        'created_by',
        'edited_by',
        'department_status'
    ];
    
    protected $casts = [
        'created_at' => 'datetime:d-M-Y',
    ];

    /**
     * Get the user who created this department
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the sub-departments for this department
     */
    public function subDepartments()
    {
        return $this->hasMany(SubDepartment::class, 'department_id');
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadgeAttribute()
    {
        return $this->department_status == 1 
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-danger">Inactive</span>';
    }
}
