<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

// Bootstrap Laravel the old way
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Creating first-time login test user...\n";

try {
    // Get first department
    $department = Department::first();
    if (!$department) {
        echo "Error: No departments found. Please run the department seeder first.\n";
        exit(1);
    }
    
    // Check if test user already exists
    $existingUser = User::where('email', 'test-first-login@savannah.com')->first();
    
    if ($existingUser) {
        echo "Test user already exists. Updating to first-time login status...\n";
        $existingUser->update([
            'is_first_login' => true,
            'password_changed_at' => null,
        ]);
        $user = $existingUser;
    } else {
        // Create new test user
        $user = User::create([
            'name' => 'Test First Login User',
            'email' => 'test-first-login@savannah.com',
            'password' => Hash::make('password123'),
            'is_first_login' => true,
            'password_changed_at' => null,
            'department_id' => $department->id,
            'email_verified_at' => now(),
        ]);
        echo "New test user created successfully!\n";
    }
    
    // Assign a basic role
    $userRole = \Spatie\Permission\Models\Role::where('name', 'Sales-Agent')->first();
    if ($userRole && !$user->hasRole($userRole)) {
        $user->assignRole($userRole);
        echo "Assigned 'Sales-Agent' role to test user.\n";
    }
    
    echo "\n=== TEST USER CREDENTIALS ===\n";
    echo "Email: test-first-login@savannah.com\n";
    echo "Password: password123\n";
    echo "Status: First-time login (will be redirected to password change)\n";
    echo "Role: Sales-Agent\n";
    echo "=============================\n\n";
    
    echo "Test user setup complete! You can now:\n";
    echo "1. Login with the test credentials above\n";
    echo "2. Verify you're redirected to password change page\n";
    echo "3. Change password and verify redirect to home page\n";
    echo "4. Logout and login again to verify normal flow\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
