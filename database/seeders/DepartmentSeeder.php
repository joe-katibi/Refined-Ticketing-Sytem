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
        DB::table('departments')->insert([
            [

            'department_name'=>'Information Technology',
            'description'=>'Information Technology',
            'created_by'=>'1',
            'department_status'=>'1'


            ],
            [

            'department_name'=>'Customer Experience',
            'description'=>'Customer Experience',
            'created_by'=>'1',
            'department_status'=>'1'

            ]
    ]);
    }
}
