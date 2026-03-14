<?php

namespace Modules\Appointment\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Appointment\Models\AppointmentStatus;
use Illuminate\Support\Facades\Auth;

class AppointmentStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default system statuses
        $defaultStatuses = [
            [
                'name' => 'scheduled-open',
                'display_name' => 'Scheduled - Open',
                'description' => 'Appointment has been scheduled but not yet started',
                'color' => '#3498db', // Blue
                'badge_class' => 'bg-primary',
                'status' => 'Active',
                'is_system' => true,
                'sort_order' => 10,
            ],
            [
                'name' => 'scheduled-closed',
                'display_name' => 'Scheduled - Closed',
                'description' => 'Scheduled appointment has been completed',
                'color' => '#2ecc71', // Green
                'badge_class' => 'bg-success',
                'status' => 'Active',
                'is_system' => true,
                'sort_order' => 20,
            ],
            [
                'name' => 'in-progress',
                'display_name' => 'In Progress',
                'description' => 'Appointment is currently being worked on',
                'color' => '#f39c12', // Orange
                'badge_class' => 'bg-warning',
                'status' => 'Active',
                'is_system' => true,
                'sort_order' => 30,
            ],
            [
                'name' => 'completed',
                'display_name' => 'Completed',
                'description' => 'Appointment has been successfully completed',
                'color' => '#27ae60', // Dark Green
                'badge_class' => 'bg-success',
                'status' => 'Active',
                'is_system' => true,
                'sort_order' => 40,
            ],
            [
                'name' => 'cancelled',
                'display_name' => 'Cancelled',
                'description' => 'Appointment has been cancelled',
                'color' => '#e74c3c', // Red
                'badge_class' => 'bg-danger',
                'status' => 'Active',
                'is_system' => true,
                'sort_order' => 50,
            ],
            [
                'name' => 'scheduled-assigned-team',
                'display_name' => 'Scheduled - Assigned Team',
                'description' => 'Appointment has been scheduled and assigned to a team',
                'color' => '#9b59b6', // Purple
                'badge_class' => 'bg-info',
                'status' => 'Active',
                'is_system' => true,
                'sort_order' => 60,
            ],
            [
                'name' => 'escalated-open',
                'display_name' => 'Escalated - Open',
                'description' => 'Appointment has been escalated and is pending resolution',
                'color' => '#e67e22', // Dark Orange
                'badge_class' => 'bg-warning',
                'status' => 'Active',
                'is_system' => true,
                'sort_order' => 70,
            ],
            [
                'name' => 'escalated-closed',
                'display_name' => 'Escalated - Closed',
                'description' => 'Escalated appointment has been resolved',
                'color' => '#16a085', // Teal
                'badge_class' => 'bg-success',
                'status' => 'Active',
                'is_system' => true,
                'sort_order' => 80,
            ],
            [
                'name' => 'escalated-infrastructure',
                'display_name' => 'Escalated - Infrastructure',
                'description' => 'Appointment has been escalated to the infrastructure team',
                'color' => '#d35400', // Burnt Orange
                'badge_class' => 'bg-warning',
                'status' => 'Active',
                'is_system' => true,
                'sort_order' => 90,
            ],
            [
                'name' => 'escalated-noc',
                'display_name' => 'Escalated - NOC',
                'description' => 'Appointment has been escalated to the Network Operations Center',
                'color' => '#c0392b', // Dark Red
                'badge_class' => 'bg-danger',
                'status' => 'Active',
                'is_system' => true,
                'sort_order' => 100,
            ],
            [
              'name' => 'support-post-install',
              'display_name' => 'Support - Post Install',
              'description' => 'Post-installation support is being provided',
              'color' => '#8e44ad', // Dark Purple
              'badge_class' => 'bg-info',
              'status' => 'Active',
              'is_system' => true,
              'sort_order' => 110,
          ],
            [
              'name' => 'escalated-design',
              'display_name' => 'Escalated - Design',
              'description' => 'Appointment has been escalated to design team',
              'color' => '#8e44ad', // Dark Purple
              'badge_class' => 'bg-info',
              'status' => 'Active',
              'is_system' => true,
              'sort_order' => 110,
          ],

        ];

        // Get the first admin user or use ID 1 if no users exist
        $adminId = 1;

        foreach ($defaultStatuses as $status) {
            AppointmentStatus::updateOrCreate(
                ['name' => $status['name']],
                [
                    'display_name' => $status['display_name'],
                    'description' => $status['description'],
                    'color' => $status['color'],
                    'badge_class' => $status['badge_class'],
                    'status' => $status['status'],
                    'is_system' => $status['is_system'],
                    'sort_order' => $status['sort_order'],
                    'created_by' => $adminId,
                    'edited_by' => $adminId,
                ]
            );
        }
    }
}
