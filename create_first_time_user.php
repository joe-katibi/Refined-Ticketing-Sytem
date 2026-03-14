<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Bootstrap Laravel
$app = Application::configure(basePath: __DIR__)
    ->withRouting(
        web: __DIR__.'/routes/web.php',
        api: __DIR__.'/routes/api.php',
        commands: __DIR__.'/routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Creating first-time login test user...\n";

try {
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
            'department_id' => 1, // Assuming department 1 exists
            'team_id' => 1, // Assuming team 1 exists
            'email_verified_at' => now(),
        ]);
        echo "New test user created successfully!\n";
    }
    
    // Assign a basic role (assuming 'User' role exists)
    $userRole = \Spatie\Permission\Models\Role::where('name', 'User')->first();
    if ($userRole) {
        $user->assignRole($userRole);
        echo "Assigned 'User' role to test user.\n";
    }
    
    echo "\n=== TEST USER CREDENTIALS ===\n";
    echo "Email: test-first-login@savannah.com\n";
    echo "Password: password123\n";
    echo "Status: First-time login (will be redirected to password change)\n";
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
