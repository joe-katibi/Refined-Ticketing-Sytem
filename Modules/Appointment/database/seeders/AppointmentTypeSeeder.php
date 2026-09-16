<?php

namespace Modules\Appointment\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Appointment\Models\AppointmentType;
use Modules\Appointment\Models\SubAppointmentType;

/**
 * Seeds the four visit types from the spec (section 4.1): Installation (INS),
 * Wi-Fi Extender (WiE), Support (SUP), Shifting (SHI). Previously nothing
 * seeded appointment_types at all, so a fresh install had an empty "Type"
 * dropdown and the Create Appointment form was unusable out of the box.
 */
class AppointmentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'type_name' => 'Installation',
                'code_prefix' => 'INS',
                'type_description' => 'New service installation visit.',
                'sub_types' => ['New Fiber Installation', 'Relocation Installation'],
            ],
            [
                'type_name' => 'Wi-Fi Extender',
                'code_prefix' => 'WiE',
                'type_description' => 'Wi-Fi extender survey/installation visit.',
                'sub_types' => ['Extender Installation', 'Extender Troubleshooting'],
            ],
            [
                'type_name' => 'Support',
                'code_prefix' => 'SUP',
                'type_description' => 'Support / repair visit.',
                'sub_types' => ['LOS Flashing Red', 'Slow Speed', 'Intermittent Connection', 'Equipment Issue'],
            ],
            [
                'type_name' => 'Shifting',
                'code_prefix' => 'SHI',
                'type_description' => 'Customer premise shifting visit.',
                'sub_types' => ['Shifting - Same Area', 'Shifting - New Area'],
            ],
        ];

        foreach ($types as $typeData) {
            $subTypes = $typeData['sub_types'];
            unset($typeData['sub_types']);

            $type = AppointmentType::firstOrCreate(
                ['type_name' => $typeData['type_name']],
                array_merge($typeData, ['type_status' => 'Active'])
            );

            // A type created before this seeder ran (e.g. via the settings UI)
            // may not have a prefix set yet.
            if (!$type->code_prefix) {
                $type->update(['code_prefix' => $typeData['code_prefix']]);
            }

            foreach ($subTypes as $subTypeName) {
                SubAppointmentType::firstOrCreate(
                    ['sub_type_name' => $subTypeName, 'appointment_type_id' => $type->id],
                    ['sub_type_description' => $subTypeName, 'sub_type_status' => 'Active']
                );
            }
        }
    }
}
