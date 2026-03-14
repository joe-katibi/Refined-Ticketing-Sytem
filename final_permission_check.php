<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

echo "Final Permission Check" . PHP_EOL;

// Clear cache
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

// Find or create user
$user = User::where('email', 'admin@admin.com')->first();
if (!$user) {
    $user = User::create([
        'name' => 'Super Admin',
        'email' => 'admin@admin.com', 
        'password' => Hash::make('password123'),
        'email_verified_at' => now(),
        'is_first_login' => false
    ]);
    echo "Created user" . PHP_EOL;
}

// Find or create role
$role = Role::where('name', 'Super-Admin')->first();
if (!$role) {
    $role = Role::create(['name' => 'Super-Admin']);
    echo "Created role" . PHP_EOL;
}

// Assign all permissions
$permissions = Permission::all();
$role->syncPermissions($permissions);
$user->syncRoles(['Super-Admin']);

// Clear cache
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
$user = $user->fresh();

echo "Permissions: " . $user->getAllPermissions()->count() . "/" . $permissions->count() . PHP_EOL;

// Test specific permissions
$testPerms = ['view-appointments-menu', 'view-appointment-list-menu'];
foreach ($testPerms as $perm) {
    echo $perm . ": " . ($user->can($perm) ? "YES" : "NO") . PHP_EOL;
}

echo "Done" . PHP_EOL;
