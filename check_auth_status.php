<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo "Authentication Status Check\n";
echo "==========================\n\n";

// Check if we have any users
$userCount = User::count();
echo "Total users in database: {$userCount}\n";

if ($userCount > 0) {
    $superAdmin = User::where('email', 'super-admin@savannah.com')->first();
    if ($superAdmin) {
        echo "✓ Super admin user exists: {$superAdmin->name}\n";
        echo "  Email: {$superAdmin->email}\n";
        echo "  Status: {$superAdmin->user_status}\n";
        echo "  Email verified: " . ($superAdmin->email_verified_at ? 'Yes' : 'No') . "\n";
        
        $roles = $superAdmin->roles()->pluck('name')->toArray();
        echo "  Roles: " . (empty($roles) ? 'None' : implode(', ', $roles)) . "\n";
    } else {
        echo "✗ Super admin user not found\n";
        echo "Available users:\n";
        $users = User::select('name', 'email')->limit(5)->get();
        foreach ($users as $user) {
            echo "  - {$user->name} ({$user->email})\n";
        }
    }
} else {
    echo "✗ No users found in database\n";
}

echo "\nTo fix 403 errors, you need to:\n";
echo "1. Login to the application first\n";
echo "2. Ensure your user has the correct roles/permissions\n";
echo "3. Clear all caches after permission changes\n";
