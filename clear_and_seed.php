<?php

// Simple script to clear and reseed permissions
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Clearing existing permissions and roles...\n";

// Clear existing data using raw SQL
DB::statement('DELETE FROM role_has_permissions');
DB::statement('DELETE FROM model_has_roles'); 
DB::statement('DELETE FROM model_has_permissions');
DB::statement('DELETE FROM permissions');
DB::statement('DELETE FROM roles');

echo "Cleared existing data.\n";

// Clear permission cache
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

echo "Running PermissionsSeeder...\n";
Artisan::call('db:seed', ['--class' => 'PermissionsSeeder']);
echo Artisan::output();

echo "Running RolesSeeder...\n";
Artisan::call('db:seed', ['--class' => 'RolesSeeder']);
echo Artisan::output();

echo "Completed successfully!\n";

// Show counts
$permissionCount = \Spatie\Permission\Models\Permission::count();
$roleCount = \Spatie\Permission\Models\Role::count();
echo "Created {$permissionCount} permissions and {$roleCount} roles.\n";
