<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

echo "Checking Create Appointment Permission\n";
echo "====================================\n\n";

// Check if permission exists
$permission = Permission::where('name', 'view-create-appointment')->first();
if ($permission) {
    echo "✓ Permission 'view-create-appointment' exists (ID: {$permission->id})\n";
} else {
    echo "✗ Permission 'view-create-appointment' NOT FOUND\n";
    echo "Creating permission...\n";
    $permission = Permission::create(['name' => 'view-create-appointment']);
    echo "✓ Created permission 'view-create-appointment' (ID: {$permission->id})\n";
}

// Check super-admin role has this permission
$superAdminRole = Role::where('name', 'Super-Admin')->first();
if ($superAdminRole) {
    if ($superAdminRole->hasPermissionTo('view-create-appointment')) {
        echo "✓ Super-Admin role has 'view-create-appointment' permission\n";
    } else {
        echo "✗ Super-Admin role missing 'view-create-appointment' permission\n";
        echo "Adding permission to Super-Admin role...\n";
        $superAdminRole->givePermissionTo('view-create-appointment');
        echo "✓ Added 'view-create-appointment' to Super-Admin role\n";
    }
}

// Check user
$user = User::where('email', 'super-admin@savannah.com')->first();
if ($user) {
    if ($user->can('view-create-appointment')) {
        echo "✓ User can access 'view-create-appointment'\n";
        echo "The Add button should now be visible!\n";
    } else {
        echo "✗ User cannot access 'view-create-appointment'\n";
    }
} else {
    echo "✗ User not found\n";
}

// Clear permission cache
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
echo "✓ Permission cache cleared\n";

echo "\nDone!\n";
