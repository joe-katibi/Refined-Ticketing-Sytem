<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Support\Str;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Define all roles with their corresponding user data
        $roleUsers = [
            [
                'role_name' => 'Super-Admin',
                'user_data' => [
                    'name' => 'Super Admin',
                    'username' => 'super-admin',
                    'email' => 'super-admin@savannah.com',
                    'is_admin' => 1,
                    'user_status' => '1',
                    'department_id' => '1',
                    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                ],
                'all_permissions' => true
            ],
            [
                'role_name' => 'Sales-Agent',
                'user_data' => [
                    'name' => 'Sales Agent',
                    'username' => 'sales-agent',
                    'email' => 'sales-agent@savannah.com',
                    'is_admin' => 0,
                    'user_status' => '1',
                    'department_id' => '2',
                    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                ]
            ],
            [
                'role_name' => 'Team-Leader-Sales',
                'user_data' => [
                    'name' => 'Team Leader Sales',
                    'username' => 'team-leader-sales',
                    'email' => 'team-leader-sales@savannah.com',
                    'is_admin' => 0,
                    'user_status' => '1',
                    'department_id' => '2',
                    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                ]
            ],
            [
                'role_name' => 'Management',
                'user_data' => [
                    'name' => 'Management User',
                    'username' => 'management',
                    'email' => 'management@savannah.com',
                    'is_admin' => 1,
                    'user_status' => '1',
                    'department_id' => '1',
                    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                ]
            ],
            [
                'role_name' => 'Call-Center-Agent',
                'user_data' => [
                    'name' => 'Call Center Agent',
                    'username' => 'call-center-agent',
                    'email' => 'call-center-agent@savannah.com',
                    'is_admin' => 0,
                    'user_status' => '1',
                    'department_id' => '3',
                    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                ]
            ],
            [
                'role_name' => 'Service-Support-Agent',
                'user_data' => [
                    'name' => 'Service Support Agent',
                    'username' => 'service-support-agent',
                    'email' => 'service-support-agent@savannah.com',
                    'is_admin' => 0,
                    'user_status' => '1',
                    'department_id' => '4',
                    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                ]
            ],
            [
                'role_name' => 'Back-office-Agent',
                'user_data' => [
                    'name' => 'Back Office Agent',
                    'username' => 'back-office-agent',
                    'email' => 'back-office-agent@savannah.com',
                    'is_admin' => 0,
                    'user_status' => '1',
                    'department_id' => '5',
                    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                ]
            ],
            [
                'role_name' => 'Team-Leader-Call-Center',
                'user_data' => [
                    'name' => 'Team Leader Call Center',
                    'username' => 'team-leader-call-center',
                    'email' => 'team-leader-call-center@savannah.com',
                    'is_admin' => 0,
                    'user_status' => '1',
                    'department_id' => '3',
                    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                ]
            ],
            [
                'role_name' => 'Team-leader-Service-Delivery',
                'user_data' => [
                    'name' => 'Team Leader Service Delivery',
                    'username' => 'team-leader-service-delivery',
                    'email' => 'team-leader-service-delivery@savannah.com',
                    'is_admin' => 0,
                    'user_status' => '1',
                    'department_id' => '6',
                    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                ]
            ],
            [
                'role_name' => 'Dispatcher-Service-Delivery',
                'user_data' => [
                    'name' => 'Dispatcher Service Delivery',
                    'username' => 'dispatcher-service-delivery',
                    'email' => 'dispatcher-service-delivery@savannah.com',
                    'is_admin' => 0,
                    'user_status' => '1',
                    'department_id' => '6',
                    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                ]
            ],
            [
                'role_name' => 'Dispatcher-Infrastructure-Tier-II-Noc',
                'user_data' => [
                    'name' => 'Dispatcher Infrastructure NOC',
                    'username' => 'dispatcher-infrastructure-noc',
                    'email' => 'dispatcher-infrastructure-noc@savannah.com',
                    'is_admin' => 0,
                    'user_status' => '1',
                    'department_id' => '7',
                    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                ]
            ],
            [
                'role_name' => 'Team-leader-noc-Infrastructure',
                'user_data' => [
                    'name' => 'Team Leader NOC Infrastructure',
                    'username' => 'team-leader-noc-infrastructure',
                    'email' => 'team-leader-noc-infrastructure@savannah.com',
                    'is_admin' => 0,
                    'user_status' => '1',
                    'department_id' => '7',
                    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                ]
            ],
            [
                'role_name' => 'Field-Technician',
                'user_data' => [
                    'name' => 'Field Technician',
                    'username' => 'field-technician',
                    'email' => 'field-technician@savannah.com',
                    'is_admin' => 0,
                    'user_status' => '1',
                    'department_id' => '8',
                    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                ]
            ]
        ];

        // Create users for each role
        foreach ($roleUsers as $roleUserData) {
            $userData = array_merge($roleUserData['user_data'], [
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]);

            $user = User::updateOrCreate(
                ['email' => $userData['email']], 
                $userData
            );
            
            // Find and assign the role
            $role = Role::where('name', $roleUserData['role_name'])->first();
            if ($role) {
                $user->assignRole($role);
                
                // Assign all permissions for Super-Admin, or role-specific permissions for others
                if (isset($roleUserData['all_permissions']) && $roleUserData['all_permissions']) {
                    $user->syncPermissions(Permission::all());
                }
            }
        }
    }
}
