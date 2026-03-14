<?php

namespace Modules\Escalations\Entities;

use Illuminate\Database\Eloquent\Model;

class EscalationList extends Model
{
    protected $table = 'lists';

    protected $fillable = [
        'account_number',
        'escalation_id',
        'ticket_id',
        'department_id',
        'sub_department_id',
        'category_id',
        'sub_category_id',
        'description',
        'priority',
        'status',
        'created_by',
        'edited_by'
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class, 'sub_category_id');
    }

    public function department()
    {
        return $this->belongsTo(\App\Models\Department::class, 'department_id');
    }

    public function sub_department()
    {
        return $this->belongsTo(\App\Models\SubDepartment::class, 'sub_department_id');
    }
}
