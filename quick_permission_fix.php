<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

echo "Quick Permission Fix Script\n";
echo "==========================\n\n";

try {
    // Clear permission cache first
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    echo "✓ Permission cache cleared\n";

    // Check if permission exists, create if missing
    $permission = Permission::firstOrCreate(['name' => 'view-appointments-menu']);
    echo "✓ Permission 'view-appointments-menu' ensured (ID: {$permission->id})\n";

    // Get Super-Admin role
    $superAdminRole = Role::where('name', 'Super-Admin')->first();
    if (!$superAdminRole) {
        $superAdminRole = Role::create(['name' => 'Super-Admin']);
        echo "✓ Created Super-Admin role\n";
    }

    // Assign ALL permissions to Super-Admin
    $allPermissions = Permission::all();
    $superAdminRole->syncPermissions($allPermissions);
    echo "✓ Assigned all {$allPermissions->count()} permissions to Super-Admin role\n";

    // Get user and assign Super-Admin role
    $user = User::where('email', 'super-admin@savannah.com')->first();
    if ($user) {
        $user->assignRole('Super-Admin');
        echo "✓ Assigned Super-Admin role to user: {$user->name}\n";
        
        // Verify permission
        if ($user->can('view-appointments-menu')) {
            echo "✅ SUCCESS: User can now access 'view-appointments-menu'\n";
        } else {
            echo "❌ FAILED: User still cannot access 'view-appointments-menu'\n";
        }
    } else {
        echo "❌ User 'super-admin@savannah.com' not found\n";
    }

    // Clear cache again
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    echo "✓ Final permission cache clear\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\nDone!\n";
