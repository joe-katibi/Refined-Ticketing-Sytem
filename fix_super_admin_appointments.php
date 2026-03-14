<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

try {
    // Get or create super-admin role
    $superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);
    echo "Super-admin role: " . ($superAdminRole->wasRecentlyCreated ? "Created" : "Found") . "\n";

    // Get all appointment permissions
    $appointmentPermissions = Permission::where('name', 'like', '%appointment%')->get();
    echo "Found " . $appointmentPermissions->count() . " appointment permissions\n";

    // Assign all appointment permissions to super-admin
    foreach ($appointmentPermissions as $permission) {
        if (!$superAdminRole->hasPermissionTo($permission->name)) {
            $superAdminRole->givePermissionTo($permission->name);
            echo "Assigned: " . $permission->name . "\n";
        } else {
            echo "Already has: " . $permission->name . "\n";
        }
    }

    // Also assign all other permissions to super-admin (since it's super-admin)
    $allPermissions = Permission::all();
    $superAdminRole->syncPermissions($allPermissions);
    
    echo "\nSuper-admin now has " . $superAdminRole->permissions->count() . " permissions\n";
    echo "SUCCESS: Super-admin role updated with all permissions\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
