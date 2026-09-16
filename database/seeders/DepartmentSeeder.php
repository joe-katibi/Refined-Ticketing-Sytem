<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
   /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Was a plain insert() with no uniqueness guard — running db:seed twice
        // (e.g. as part of a deploy script, or by hand while diagnosing empty
        // lookup tables) silently duplicated both departments every time.
        foreach ([
            ['department_name' => 'Information Technology', 'description' => 'Information Technology'],
            ['department_name' => 'Customer Experience', 'description' => 'Customer Experience'],
        ] as $department) {
            DB::table('departments')->updateOrInsert(
                ['department_name' => $department['department_name']],
                array_merge($department, ['created_by' => 1, 'department_status' => 1])
            );
        }
    }
}
