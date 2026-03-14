<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

echo "=== DEBUGGING USER PERMISSIONS ===\n\n";

// Check all users and their roles
$users = User::with('roles', 'permissions')->get();
echo "📋 USERS IN SYSTEM:\n";
foreach ($users as $user) {
    echo "User: {$user->name} ({$user->email})\n";
    echo "  ID: {$user->id}\n";
    echo "  Roles: " . $user->roles->pluck('name')->join(', ') . "\n";
    echo "  Direct Permissions: " . $user->permissions->count() . "\n";
    echo "  All Permissions: " . $user->getAllPermissions()->count() . "\n";
    echo "  Has super-admin role: " . ($user->hasRole('super-admin') ? 'YES' : 'NO') . "\n";
    echo "  Has view-appointments-menu: " . ($user->can('view-appointments-menu') ? 'YES' : 'NO') . "\n\n";
}

// Check super-admin role
$superAdmin = Role::with('permissions')->where('name', 'super-admin')->first();
if ($superAdmin) {
    echo "🔑 SUPER-ADMIN ROLE:\n";
    echo "  Name: {$superAdmin->name}\n";
    echo "  Permissions count: " . $superAdmin->permissions->count() . "\n";
    echo "  Has view-appointments-menu: " . ($superAdmin->hasPermissionTo('view-appointments-menu') ? 'YES' : 'NO') . "\n\n";
} else {
    echo "❌ SUPER-ADMIN ROLE NOT FOUND!\n\n";
}

// Check specific permission
$permission = Permission::where('name', 'view-appointments-menu')->first();
if ($permission) {
    echo "✅ PERMISSION 'view-appointments-menu' EXISTS\n";
    echo "  ID: {$permission->id}\n";
    echo "  Guard: {$permission->guard_name}\n\n";
} else {
    echo "❌ PERMISSION 'view-appointments-menu' NOT FOUND!\n\n";
}

// Force assign super-admin to first user
$firstUser = User::first();
if ($firstUser) {
    echo "🔧 FORCING SUPER-ADMIN ASSIGNMENT:\n";
    
    // Create super-admin role if it doesn't exist
    $superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);
    
    // Assign all permissions to super-admin
    $allPermissions = Permission::all();
    $superAdminRole->syncPermissions($allPermissions);
    
    // Assign role to user
    $firstUser->assignRole('super-admin');
    
    echo "  Assigned super-admin role to: {$firstUser->name}\n";
    echo "  Super-admin now has: " . $superAdminRole->permissions->count() . " permissions\n";
    echo "  User now has: " . $firstUser->fresh()->getAllPermissions()->count() . " permissions\n";
}

echo "\n🎯 SOLUTION: Please log out and log back in, then try again.\n";
