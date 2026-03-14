<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

echo "=== FIXING SUPER ADMIN PERMISSIONS ===\n";

// Clear permission cache
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

// Find or create user
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
    echo "✓ Found existing user: {$user->name}\n";
}

// Find or create Super-Admin role
$role = Role::where('name', 'Super-Admin')->first();
if (!$role) {
    $role = Role::create(['name' => 'Super-Admin']);
    echo "✓ Created Super-Admin role\n";
} else {
    echo "✓ Found existing Super-Admin role\n";
}

// Get all permissions
$permissions = Permission::all();
echo "✓ Total permissions available: " . $permissions->count() . "\n";

// Assign all permissions to role
$role->syncPermissions($permissions);
echo "✓ Assigned all permissions to Super-Admin role\n";

// Assign role to user
$user->syncRoles(['Super-Admin']);
echo "✓ Assigned Super-Admin role to user\n";

// Clear cache again
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

// Verify permissions
$user = $user->fresh();
echo "✓ User now has " . $user->getAllPermissions()->count() . " permissions\n";

// Test appointment permissions specifically
$appointmentPermissions = [
    'view-appointment-menu',
    'view-appointments-menu', 
    'view-appointment-list-menu',
    'view-assigned-appointments-menu',
    'view-my-appointments-menu'
];

echo "\n=== APPOINTMENT PERMISSIONS TEST ===\n";
foreach ($appointmentPermissions as $permission) {
    $hasPermission = $user->can($permission);
    $status = $hasPermission ? '✓ YES' : '✗ NO';
    echo "{$status} {$permission}\n";
}

echo "\n=== SETUP COMPLETE ===\n";
echo "Super Admin: admin@admin.com / password123\n";
echo "Total permissions: " . $user->getAllPermissions()->count() . "\n";
