<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

echo "=== FIXING SUPER ADMIN PERMISSIONS ===\n";

// Clear all permission caches
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
\Illuminate\Support\Facades\Cache::flush();

// Find or create the Super Admin user
$user = User::where('email', 'admin@admin.com')->first();
if (!$user) {
    $user = User::create([
        'name' => 'Super Admin',
        'email' => 'admin@admin.com',
        'password' => Hash::make('password123'),
        'email_verified_at' => now(),
        'is_first_login' => false,
    ]);
    echo "✓ Created Super Admin user\n";
} else {
    echo "✓ Found existing Super Admin user: {$user->name}\n";
}

// Remove all existing roles from user
$user->roles()->detach();
echo "✓ Cleared existing roles\n";

// Find or create Super-Admin role
$role = Role::where('name', 'Super-Admin')->first();
if (!$role) {
    $role = Role::create([
        'name' => 'Super-Admin',
        'guard_name' => 'web'
    ]);
    echo "✓ Created Super-Admin role\n";
} else {
    echo "✓ Found existing Super-Admin role\n";
}

// Get all permissions and assign to role
$permissions = Permission::all();
echo "✓ Found {$permissions->count()} total permissions\n";

$role->permissions()->detach();
$role->givePermissionTo($permissions);
echo "✓ Assigned all permissions to Super-Admin role\n";

// Assign role to user
$user->assignRole($role);
echo "✓ Assigned Super-Admin role to user\n";

// Clear caches again
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

// Verify the setup
$user = $user->fresh();
$userPermissions = $user->getAllPermissions();
echo "✓ User now has {$userPermissions->count()} permissions\n";

// Test specific permissions
$testPermissions = [
    'view-list-user',
    'view-create-user', 
    'view-view-user',
    'view-edit-user',
    'view-users-management-menu'
];

echo "\n=== PERMISSION VERIFICATION ===\n";
foreach ($testPermissions as $permission) {
    $hasPermission = $user->can($permission);
    $status = $hasPermission ? '✓ YES' : '✗ NO';
    echo "Can '{$permission}': {$status}\n";
}

echo "\n=== SETUP COMPLETE ===\n";
echo "Super Admin user: admin@admin.com\n";
echo "Password: password123\n";
echo "Total permissions: {$userPermissions->count()}\n";
