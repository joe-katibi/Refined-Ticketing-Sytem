<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

// Clear permission cache
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

// Get all users
$users = User::all();
echo "Found " . $users->count() . " users:\n";

foreach ($users as $user) {
    echo "- {$user->name} ({$user->email})\n";
    echo "  Current roles: " . $user->roles->pluck('name')->join(', ') . "\n";
    
    // Assign super-admin role to all users for testing
    if (!$user->hasRole('super-admin')) {
        $user->assignRole('super-admin');
        echo "  ✅ Assigned super-admin role\n";
    } else {
        echo "  ℹ️  Already has super-admin role\n";
    }
    
    echo "  Permissions count: " . $user->getAllPermissions()->count() . "\n\n";
}

// Verify super-admin role has all permissions
$superAdmin = Role::where('name', 'super-admin')->first();
if ($superAdmin) {
    $allPermissions = Permission::all();
    $superAdmin->syncPermissions($allPermissions);
    echo "✅ Super-admin role updated with " . $superAdmin->permissions->count() . " permissions\n";
} else {
    echo "❌ Super-admin role not found!\n";
}

echo "\n🎉 All users now have super-admin access. Please refresh your browser.\n";
