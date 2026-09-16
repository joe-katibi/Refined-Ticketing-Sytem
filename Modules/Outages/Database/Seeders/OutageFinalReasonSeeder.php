<?php

namespace Modules\Outages\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Outages\Models\OutageFinalReason;

class OutageFinalReasonSeeder extends Seeder
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

        $finalReasons = [
            [
                'final_reason_name' => 'Power Outage Resolved',
                'final_reason_description' => 'Outage was caused by power failure and has been resolved after power restoration.',
                'final_reason_status' => 'Active',
                'created_by' => $firstUserId,
            ],
            [
                'final_reason_name' => 'Equipment Failure Fixed',
                'final_reason_description' => 'Network equipment failure was identified and replaced/repaired successfully.',
                'final_reason_status' => 'Active',
                'created_by' => $firstUserId,
            ],
            [
                'final_reason_name' => 'Fiber Cut Repaired',
                'final_reason_description' => 'Fiber optic cable was damaged and has been spliced/repaired.',
                'final_reason_status' => 'Active',
                'created_by' => $firstUserId,
            ],
            [
                'final_reason_name' => 'Configuration Error Corrected',
                'final_reason_description' => 'Network configuration issue was identified and corrected.',
                'final_reason_status' => 'Active',
                'created_by' => $firstUserId,
            ],
            [
                'final_reason_name' => 'Third Party Provider Issue',
                'final_reason_description' => 'Issue was with upstream provider and has been resolved by them.',
                'final_reason_status' => 'Active',
                'created_by' => $firstUserId,
            ],
            [
                'final_reason_name' => 'Scheduled Maintenance Completed',
                'final_reason_description' => 'Planned maintenance activity completed successfully.',
                'final_reason_status' => 'Active',
                'created_by' => $firstUserId,
            ],
            [
                'final_reason_name' => 'False Alarm',
                'final_reason_description' => 'No actual outage occurred - monitoring system false positive.',
                'final_reason_status' => 'Active',
                'created_by' => $firstUserId,
            ],
            [
                'final_reason_name' => 'Customer Equipment Issue',
                'final_reason_description' => 'Issue was with customer premises equipment, not network infrastructure.',
                'final_reason_status' => 'Active',
                'created_by' => $firstUserId,
            ],
            [
                'final_reason_name' => 'Weather Related - Resolved',
                'final_reason_description' => 'Outage caused by weather conditions, resolved after weather cleared.',
                'final_reason_status' => 'Active',
                'created_by' => $firstUserId,
            ],
            [
                'final_reason_name' => 'Software Bug Fixed',
                'final_reason_description' => 'Network software bug identified and patched.',
                'final_reason_status' => 'Active',
                'created_by' => $firstUserId,
            ],
        ];

        foreach ($finalReasons as $reason) {
            OutageFinalReason::firstOrCreate(
                ['final_reason_name' => $reason['final_reason_name']],
                $reason
            );
        }

        Model::reguard();
    }
}
