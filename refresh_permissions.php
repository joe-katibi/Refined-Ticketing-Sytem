<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting permissions refresh...\n";

try {
    // Clear existing data
    echo "Clearing existing permissions and roles...\n";
    
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    DB::table('role_has_permissions')->truncate();
    DB::table('model_has_roles')->truncate();
    DB::table('model_has_permissions')->truncate();
    DB::table('permissions')->truncate();
    DB::table('roles')->truncate();
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    echo "Existing data cleared.\n";

    // Clear cache
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    // Run seeders
    echo "Running PermissionsSeeder...\n";
    $seeder = new \Database\Seeders\PermissionsSeeder();
    $seeder->run();

    echo "Running RolesSeeder...\n";
    $roleSeeder = new \Database\Seeders\RolesSeeder();
    $roleSeeder->run();

    // Show summary
    $permissionCount = Permission::count();
    $roleCount = Role::count();
    
    echo "Permissions refresh completed successfully!\n";
    echo "Created {$permissionCount} permissions and {$roleCount} roles.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
