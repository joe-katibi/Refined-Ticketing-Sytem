<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubDepartmentSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    // Get department IDs
    $itDepartment = DB::table('departments')
      ->where('department_name', 'Information Technology')
      ->first();
    $cxDepartment = DB::table('departments')
      ->where('department_name', 'Customer Experience')
      ->first();

    $subDepartments = [];

    // Information Technology sub departments
    if ($itDepartment) {
      $subDepartments[] = [
        'sub_department_name' => 'Infrastructure',
        'department_id' => $itDepartment->id,
        'created_by' => 1,
        'sub_department_status' => 1,
        'created_at' => now(),
        'updated_at' => now(),
      ];
      $subDepartments[] = [
        'sub_department_name' => 'Network Operations Center (NOC)',
        'department_id' => $itDepartment->id,
        'created_by' => 1,
        'sub_department_status' => 1,
        'created_at' => now(),
        'updated_at' => now(),
      ];
      $subDepartments[] = [
        'sub_department_name' => 'Service Delivery',
        'department_id' => $itDepartment->id,
        'created_by' => 1,
        'sub_department_status' => 1,
        'created_at' => now(),
        'updated_at' => now(),
      ];
    }

    // Customer Experience sub departments
    if ($cxDepartment) {
      $subDepartments[] = [
        'sub_department_name' => 'Call Center',
        'department_id' => $cxDepartment->id,
        'created_by' => 1,
        'sub_department_status' => 1,
        'created_at' => now(),
        'updated_at' => now(),
      ];
      $subDepartments[] = [
        'sub_department_name' => 'Sales',
        'department_id' => $cxDepartment->id,
        'created_by' => 1,
        'sub_department_status' => 1,
        'created_at' => now(),
        'updated_at' => now(),
      ];
      $subDepartments[] = [
        'sub_department_name' => 'Back Office',
        'department_id' => $cxDepartment->id,
        'created_by' => 1,
        'sub_department_status' => 1,
        'created_at' => now(),
        'updated_at' => now(),
      ];
    }

    DB::table('sub_departments')->insert($subDepartments);
  }
}
