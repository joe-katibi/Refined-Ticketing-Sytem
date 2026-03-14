<?php

namespace App\Console\Commands;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Console\Command;

class AssignSuperAdminRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:assign-super-admin {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign Super-Admin role to a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? 'admin@admin.com';
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email {$email} not found!");
            return 1;
        }
        
        $role = Role::findByName('Super-Admin');
        
        if (!$role) {
            $this->error('Super-Admin role not found! Run php artisan db:seed --class=RolesSeeder first.');
            return 1;
        }
        
        if ($user->hasRole('Super-Admin')) {
            $this->info("User {$user->name} already has Super-Admin role.");
            $this->info("Total permissions: " . $user->getAllPermissions()->count());
            return 0;
        }
        
        $user->assignRole($role);
        
        $this->info("Successfully assigned Super-Admin role to {$user->name} ({$user->email})");
        $this->info("Total permissions: " . $user->getAllPermissions()->count());
        
        return 0;
    }
}
