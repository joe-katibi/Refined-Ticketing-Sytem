<?php

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

require_once __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

// Clear permission cache first
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

// Get or create super-admin role
$superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);

// Get all permissions and assign to super-admin
$allPermissions = Permission::all();
$superAdminRole->syncPermissions($allPermissions);

echo "Super-admin role now has " . $superAdminRole->permissions->count() . " permissions\n";

// Find your user and assign super-admin role
$adminUsers = User::whereIn('email', ['admin@example.com', 'admin@admin.com'])
    ->orWhere('name', 'like', '%admin%')
    ->get();

foreach ($adminUsers as $user) {
    $user->assignRole('super-admin');
    echo "Assigned super-admin role to: " . $user->name . " (" . $user->email . ")\n";
}

echo "Done! Please refresh your browser and try again.\n";
