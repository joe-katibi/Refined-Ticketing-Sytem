<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\SubDepartment;
use App\Models\TeamType;
use App\Models\SubTeamType;

/**
 * Seeds baseline Team Types / Sub Team Types. Previously nothing seeded
 * team_types at all, so the "Assigned Team Type" dropdown on Outage
 * creation/assignment (and equivalents elsewhere) was empty on a fresh
 * install.
 */
class TeamTypeSeeder extends Seeder
{
    public function run(): void
    {
        $itDepartment = Department::where('department_name', 'Information Technology')->first();
        $infrastructureSubDept = SubDepartment::where('sub_department_name', 'Infrastructure')->first();
        $nocSubDept = SubDepartment::where('sub_department_name', 'Network Operations Center (NOC)')->first();
        $serviceDeliverySubDept = SubDepartment::where('sub_department_name', 'Service Delivery')->first();

        if (!$itDepartment) {
            return;
        }

        $teamTypes = [
            [
                'type_name' => 'Field Technicians',
                'description' => 'Field technicians who carry out on-site installation/repair work.',
                'sub_department_id' => $serviceDeliverySubDept?->id,
                'sub_types' => ['Installation Team', 'Support Team', 'Shifting Team'],
            ],
            [
                'type_name' => 'NOC',
                'description' => 'Network Operations Center — outage detection and escalation.',
                'sub_department_id' => $nocSubDept?->id,
                'sub_types' => ['Tier 1 NOC', 'Tier 2 NOC'],
            ],
            [
                'type_name' => 'Infrastructure',
                'description' => 'Infrastructure/OLT and FTTH network maintenance team.',
                'sub_department_id' => $infrastructureSubDept?->id,
                'sub_types' => ['Access Team', 'Design Team'],
            ],
        ];

        foreach ($teamTypes as $teamTypeData) {
            $subTypes = $teamTypeData['sub_types'];
            unset($teamTypeData['sub_types']);

            $teamType = TeamType::firstOrCreate(
                ['type_name' => $teamTypeData['type_name']],
                array_merge($teamTypeData, [
                    'status' => 'Active',
                    'department_id' => $itDepartment->id,
                ])
            );

            foreach ($subTypes as $subTypeName) {
                SubTeamType::firstOrCreate(
                    ['sub_type_name' => $subTypeName, 'team_type_id' => $teamType->id],
                    [
                        'sub_type_description' => $subTypeName,
                        'sub_type_status' => 'Active',
                        'department_id' => $itDepartment->id,
                        'sub_department_id' => $teamTypeData['sub_department_id'],
                    ]
                );
            }
        }
    }
}
