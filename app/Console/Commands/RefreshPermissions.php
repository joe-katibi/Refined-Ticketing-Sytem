<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesSeeder;

class RefreshPermissions extends Command
{
    protected $signature = 'permissions:refresh';
    protected $description = 'Clear existing permissions and roles, then seed new ones';

    public function handle()
    {
        $this->info('Starting permissions refresh...');

        try {
            // Clear existing data
            $this->info('Clearing existing permissions and roles...');
            
            DB::statement('DELETE FROM role_has_permissions');
            DB::statement('DELETE FROM model_has_roles');
            DB::statement('DELETE FROM model_has_permissions');
            DB::statement('DELETE FROM permissions');
            DB::statement('DELETE FROM roles');

            $this->info('Existing data cleared.');

            // Clear cache
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            // Seed new permissions directly
            $this->info('Seeding new permissions...');
            $permissionsSeeder = new PermissionsSeeder();
            $permissionsSeeder->run();

            // Seed new roles directly
            $this->info('Seeding new roles...');
            $rolesSeeder = new RolesSeeder();
            $rolesSeeder->run();

            $this->info('Permissions refresh completed successfully!');
            
            // Show summary
            $permissionCount = Permission::count();
            $roleCount = Role::count();
            
            $this->info("Created {$permissionCount} permissions and {$roleCount} roles.");
            
        } catch (\Exception $e) {
            $this->error('Error during permissions refresh: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
