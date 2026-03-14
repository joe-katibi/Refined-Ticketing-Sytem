<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

echo "=== Super Admin Permission Check ===" . PHP_EOL;

// Clear permission cache
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

// Find or create Super Admin user
$user = User::where('email', 'admin@admin.com')->first();
if (!$user) {
    $user = User::create([
        'name' => 'Super Admin',
        'email' => 'admin@admin.com',
        'password' => Hash::make('password123'),
        'email_verified_at' => now(),
        'is_first_login' => false
    ]);
    echo "✓ Created Super Admin user" . PHP_EOL;
} else {
    echo "✓ Found Super Admin user: " . $user->name . PHP_EOL;
}

// Find or create Super-Admin role
$role = Role::where('name', 'Super-Admin')->first();
if (!$role) {
    $role = Role::create(['name' => 'Super-Admin']);
    echo "✓ Created Super-Admin role" . PHP_EOL;
} else {
    echo "✓ Found Super-Admin role" . PHP_EOL;
}

// Get all permissions
$permissions = Permission::all();
echo "✓ Total permissions in database: " . $permissions->count() . PHP_EOL;

// Sync all permissions to Super-Admin role
$role->syncPermissions($permissions);
echo "✓ Synced all permissions to Super-Admin role" . PHP_EOL;

// Assign Super-Admin role to user
$user->syncRoles(['Super-Admin']);
echo "✓ Assigned Super-Admin role to user" . PHP_EOL;

// Clear cache again
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

// Refresh user instance
$user = $user->fresh();

echo PHP_EOL . "=== Permission Verification ===" . PHP_EOL;
echo "User has " . $user->getAllPermissions()->count() . " permissions" . PHP_EOL;

// Check specific appointment permissions
$appointmentPermissions = [
    'view-appointments-menu',
    'view-appointment-list-menu',
    'view-assigned-appointments-menu',
    'view-my-appointments-menu',
    'view-create-appointment',
    'view-edit-appointment',
    'view-view-appointment',
    'view-delete-appointment'
];

echo PHP_EOL . "=== Appointment Permissions Check ===" . PHP_EOL;
foreach ($appointmentPermissions as $permission) {
    $hasPermission = $user->can($permission);
    $exists = Permission::where('name', $permission)->exists();
    echo "- {$permission}: " . ($hasPermission ? "✓ HAS" : "✗ MISSING") . 
         " (exists in DB: " . ($exists ? "YES" : "NO") . ")" . PHP_EOL;
}

echo PHP_EOL . "=== Role Check ===" . PHP_EOL;
echo "User roles: " . $user->roles->pluck('name')->implode(', ') . PHP_EOL;
echo "Role permissions: " . $role->permissions->count() . PHP_EOL;

echo PHP_EOL . "Setup complete!" . PHP_EOL;
