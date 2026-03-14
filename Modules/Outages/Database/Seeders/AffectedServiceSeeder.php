<?php

namespace Modules\Outages\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Outages\Models\AffectedService;

class AffectedServiceSeeder extends Seeder
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

        $affectedServices = [
            [
                'service_name' => 'Internet Service',
                'service_description' => 'Residential and business internet connectivity services.',
                'service_status' => 'Active',
                'created_by' => $firstUserId,
            ],
            [
                'service_name' => 'Voice Service',
                'service_description' => 'VoIP and traditional voice communication services.',
                'service_status' => 'Active',
                'created_by' => $firstUserId,
            ],
            [
                'service_name' => 'IPTV Service',
                'service_description' => 'Internet Protocol Television and streaming services.',
                'service_status' => 'Active',
                'created_by' => $firstUserId,
            ],
            [
                'service_name' => 'Business Solutions',
                'service_description' => 'Enterprise-grade business connectivity and solutions.',
                'service_status' => 'Active',
                'created_by' => $firstUserId,
            ],
            [
                'service_name' => 'Cloud Services',
                'service_description' => 'Cloud hosting, storage, and computing services.',
                'service_status' => 'Active',
                'created_by' => $firstUserId,
            ],
            [
                'service_name' => 'Email Services',
                'service_description' => 'Email hosting and communication services.',
                'service_status' => 'Active',
                'created_by' => $firstUserId,
            ],
            [
                'service_name' => 'Web Hosting',
                'service_description' => 'Website hosting and domain management services.',
                'service_status' => 'Active',
                'created_by' => $firstUserId,
            ],
            [
                'service_name' => 'Security Services',
                'service_description' => 'Cybersecurity, firewall, and threat protection services.',
                'service_status' => 'Active',
                'created_by' => $firstUserId,
            ],
            [
                'service_name' => 'Backup Services',
                'service_description' => 'Data backup and disaster recovery services.',
                'service_status' => 'Active',
                'created_by' => $firstUserId,
            ],
            [
                'service_name' => 'Support Services',
                'service_description' => 'Technical support and customer service operations.',
                'service_status' => 'Active',
                'created_by' => $firstUserId,
            ],
        ];

        foreach ($affectedServices as $service) {
            AffectedService::create($service);
        }

        Model::reguard();
    }
}
