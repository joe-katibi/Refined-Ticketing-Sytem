<?php

namespace Modules\Outages\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Outages\Models\AffectedArea;

class AffectedAreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // Get the first available user ID, or null if no users exist
        $firstUserId = \App\Models\User::first()?->id;

        $affectedAreas = [
            [
                'area_name' => 'Network Infrastructure',
                'area_description' => 'Core network infrastructure including routers, switches, and backbone connections.',
                'area_status' => 'Active',
                'created_by' => $firstUserId,
            ],
            [
                'area_name' => 'Customer Services',
                'area_description' => 'Customer-facing services and support systems.',
                'area_status' => 'Active',
                'created_by' => $firstUserId,
            ],
            [
                'area_name' => 'Data Center',
                'area_description' => 'Primary and secondary data center facilities and equipment.',
                'area_status' => 'Active',
                'created_by' => $firstUserId,
            ],
            [
                'area_name' => 'Fiber Network',
                'area_description' => 'Fiber optic cable infrastructure and related equipment.',
                'area_status' => 'Active',
                'created_by' => $firstUserId,
            ],
            [
                'area_name' => 'Wireless Network',
                'area_description' => 'Wireless access points, towers, and radio equipment.',
                'area_status' => 'Active',
                'created_by' => $firstUserId,
            ],
            [
                'area_name' => 'Power Systems',
                'area_description' => 'Electrical power infrastructure including UPS, generators, and power distribution.',
                'area_status' => 'Active',
                'created_by' => $firstUserId,
            ],
            [
                'area_name' => 'Cooling Systems',
                'area_description' => 'HVAC and cooling systems for network equipment.',
                'area_status' => 'Active',
                'created_by' => $firstUserId,
            ],
            [
                'area_name' => 'Security Systems',
                'area_description' => 'Physical and network security infrastructure.',
                'area_status' => 'Active',
                'created_by' => $firstUserId,
            ],
        ];

        foreach ($affectedAreas as $area) {
            AffectedArea::firstOrCreate(
                ['area_name' => $area['area_name']],
                $area
            );
        }

        Model::reguard();
    }
}
