<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Starting Super Admin permissions fix...\n";
    
    // Get or create Super-Admin role
    $superAdminRole = Role::firstOrCreate(['name' => 'Super-Admin']);
    echo "Super-Admin role found/created.\n";
    
    // Get all permissions
    $allPermissions = Permission::all();
    echo "Found " . $allPermissions->count() . " permissions.\n";
    
    // Assign all permissions to Super-Admin
    $superAdminRole->syncPermissions($allPermissions);
    echo "All permissions assigned to Super-Admin role.\n";
    
    // Verify the assignment
    $assignedPermissions = $superAdminRole->permissions()->count();
    echo "Super-Admin now has " . $assignedPermissions . " permissions assigned.\n";
    
    // Find Super Admin user and assign role
    $superAdminUser = \App\Models\User::where('email', 'super-admin@savannah.com')->first();
    if ($superAdminUser) {
        $superAdminUser->assignRole('Super-Admin');
        echo "Super-Admin role assigned to user: " . $superAdminUser->email . "\n";
        
        // Verify user permissions
        $userPermissions = $superAdminUser->getAllPermissions()->count();
        echo "User now has access to " . $userPermissions . " permissions.\n";
    } else {
        echo "Super Admin user not found. Please create user first.\n";
    }
    
    echo "Super Admin permissions fix completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
