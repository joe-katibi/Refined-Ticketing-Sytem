<?php

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

require_once __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

// Clear all caches
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

// Get or create super-admin role
$superAdmin = Role::firstOrCreate(['name' => 'super-admin']);

// Get ALL permissions and assign to super-admin
$allPermissions = Permission::all();
$superAdmin->syncPermissions($allPermissions);

// Get all users and assign super-admin role
$users = User::all();
foreach ($users as $user) {
    $user->syncRoles(['super-admin']);
    echo "✅ User {$user->name} ({$user->email}) now has super-admin role\n";
}

echo "\n📊 FINAL STATUS:\n";
echo "Super-admin role has " . $superAdmin->permissions->count() . " permissions\n";
echo "Total users with super-admin: " . User::role('super-admin')->count() . "\n";

// Check specific permission
$appointmentPerm = Permission::where('name', 'view-appointments-menu')->first();
if ($appointmentPerm && $superAdmin->hasPermissionTo('view-appointments-menu')) {
    echo "✅ Super-admin has view-appointments-menu permission\n";
} else {
    echo "❌ Super-admin missing view-appointments-menu permission\n";
}

echo "\n🔄 PLEASE LOG OUT AND LOG BACK IN TO REFRESH YOUR SESSION\n";
