<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Login Debug Test ===\n";

try {
    // Test 1: Check if admin user exists and password is correct
    echo "1. Checking admin user...\n";
    $admin = User::where('email', 'admin@test.com')->first();
    
    if (!$admin) {
        echo "❌ Admin user not found!\n";
        exit;
    }
    
    echo "✅ Admin user found: {$admin->name} ({$admin->email})\n";
    echo "User ID: {$admin->id}\n";
    echo "Email verified: " . ($admin->email_verified_at ? 'Yes' : 'No') . "\n";
    echo "Team Type ID: {$admin->team_type_id}\n";
    
    // Test 2: Check password hash
    echo "\n2. Testing password...\n";
    $passwordCheck = Hash::check('password', $admin->password);
    echo "Password check result: " . ($passwordCheck ? '✅ Valid' : '❌ Invalid') . "\n";
    
    // Test 3: Test Auth::attempt manually
    echo "\n3. Testing Auth::attempt...\n";
    $credentials = ['email' => 'admin@test.com', 'password' => 'password'];
    
    // Clear any existing auth
    Auth::logout();
    
    $authResult = Auth::attempt($credentials);
    echo "Auth::attempt result: " . ($authResult ? '✅ Success' : '❌ Failed') . "\n";
    
    if ($authResult) {
        $authenticatedUser = Auth::user();
        echo "Authenticated user: {$authenticatedUser->name}\n";
        echo "Auth guard: " . Auth::getDefaultDriver() . "\n";
    }
    
    // Test 4: Check session configuration
    echo "\n4. Session configuration...\n";
    echo "Session driver: " . config('session.driver') . "\n";
    echo "Session domain: " . config('session.domain') . "\n";
    echo "Session secure: " . (config('session.secure') ? 'true' : 'false') . "\n";
    echo "APP_URL: " . config('app.url') . "\n";
    
    // Test 5: Check if user has any specific attributes that might cause issues
    echo "\n5. User attributes check...\n";
    echo "is_first_login: " . ($admin->is_first_login ?? 'null') . "\n";
    echo "created_at: {$admin->created_at}\n";
    echo "updated_at: {$admin->updated_at}\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
