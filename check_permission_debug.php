<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

echo "=== Permission Debug Check ===\n\n";

// Check if permission exists
$permission = Permission::where('name', 'view-appointments-menu')->first();
if ($permission) {
    echo "✓ Permission 'view-appointments-menu' exists (ID: {$permission->id})\n";
} else {
    echo "✗ Permission 'view-appointments-menu' NOT FOUND\n";
    echo "Available appointment permissions:\n";
    $appointmentPerms = Permission::where('name', 'like', '%appointment%')->pluck('name');
    foreach ($appointmentPerms as $perm) {
        echo "  - {$perm}\n";
    }
}

// Check super-admin role
$superAdminRole = Role::where('name', 'Super-Admin')->first();
if ($superAdminRole) {
    echo "✓ Super-Admin role exists (ID: {$superAdminRole->id})\n";
    $permCount = $superAdminRole->permissions()->count();
    echo "  - Has {$permCount} permissions\n";
    
    if ($permission && $superAdminRole->hasPermissionTo('view-appointments-menu')) {
        echo "  ✓ Super-Admin has 'view-appointments-menu' permission\n";
    } else {
        echo "  ✗ Super-Admin does NOT have 'view-appointments-menu' permission\n";
    }
} else {
    echo "✗ Super-Admin role NOT FOUND\n";
}

// Check user
$user = User::where('email', 'super-admin@savannah.com')->first();
if ($user) {
    echo "✓ User 'super-admin@savannah.com' exists (ID: {$user->id})\n";
    $roles = $user->roles()->pluck('name')->toArray();
    echo "  - Roles: " . implode(', ', $roles) . "\n";
    
    if ($user->can('view-appointments-menu')) {
        echo "  ✓ User CAN access 'view-appointments-menu'\n";
    } else {
        echo "  ✗ User CANNOT access 'view-appointments-menu'\n";
    }
} else {
    echo "✗ User 'super-admin@savannah.com' NOT FOUND\n";
}

echo "\n=== End Debug ===\n";
