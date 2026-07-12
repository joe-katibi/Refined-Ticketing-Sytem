<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

echo "Super Admin Verification" . PHP_EOL;

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
    echo "Created Super Admin user" . PHP_EOL;
} else {
    echo "Found Super Admin user" . PHP_EOL;
}

// Find or create Super-Admin role
$role = Role::where('name', 'Super-Admin')->first();
if (!$role) {
    $role = Role::create(['name' => 'Super-Admin']);
    echo "Created Super-Admin role" . PHP_EOL;
}

// Sync all permissions to role
$permissions = Permission::all();
$role->syncPermissions($permissions);
$user->syncRoles(['Super-Admin']);

// Clear cache and refresh
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
$user = $user->fresh();

echo "Total permissions: " . $permissions->count() . PHP_EOL;
echo "User permissions: " . $user->getAllPermissions()->count() . PHP_EOL;

// Test appointment permissions
$appointmentPerms = ['view-appointments-menu', 'view-appointment-list-menu'];
foreach ($appointmentPerms as $perm) {
    $exists = Permission::where('name', $perm)->exists();
    $canAccess = $user->can($perm);
    echo $perm . " - exists: " . ($exists ? "YES" : "NO") . " - can access: " . ($canAccess ? "YES" : "NO") . PHP_EOL;
}

echo "Verification complete" . PHP_EOL;
