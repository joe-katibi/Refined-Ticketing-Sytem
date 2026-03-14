<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

// Clear cached permissions
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

// Create or get super-admin role
$superAdmin = Role::firstOrCreate(['name' => 'super-admin']);

// Get all permissions
$permissions = Permission::all();

// Assign all permissions to super-admin
$superAdmin->syncPermissions($permissions);

echo "✅ Super-admin role now has " . $superAdmin->permissions->count() . " permissions\n";

// Find and assign super-admin role to admin users
$users = User::where('email', 'like', '%admin%')->orWhere('name', 'like', '%admin%')->get();

foreach ($users as $user) {
    if (!$user->hasRole('super-admin')) {
        $user->assignRole('super-admin');
        echo "✅ Assigned super-admin role to: {$user->name} ({$user->email})\n";
    } else {
        echo "ℹ️  User {$user->name} already has super-admin role\n";
    }
}

echo "\n🎉 Done! Please refresh your browser and try accessing the appointment page again.\n";
