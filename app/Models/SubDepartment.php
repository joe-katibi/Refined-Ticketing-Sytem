<?php

namespace App\Models;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubDepartment extends Model
{
    use HasFactory;

    protected $fillable=[
      'sub_department_name',
      'department_id',
      'created_by',
      'edited_by',
      'sub_department_status'


  ];
  protected $casts = [
      'created_at' => 'datetime:d-M-Y',
  ];


  public function department()
  {
      return $this->belongsTo(Department::class , 'department_id' , 'id');
  }
}
