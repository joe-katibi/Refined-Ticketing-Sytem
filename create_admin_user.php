<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class);

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== Creating Admin User ===\n";

try {
    // Check if admin user already exists
    $existingAdmin = User::where('email', 'admin@test.com')->first();
    
    if ($existingAdmin) {
        echo "Admin user already exists: " . $existingAdmin->email . "\n";
        echo "Updating password to 'password'...\n";
        
        $existingAdmin->password = Hash::make('password');
        $existingAdmin->save();
        
        echo "✅ Password updated for: " . $existingAdmin->email . "\n";
    } else {
        // Create new admin user
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'team_type_id' => 1, // Default team
        ]);
        
        echo "✅ Admin user created successfully!\n";
        echo "Email: " . $admin->email . "\n";
        echo "Password: password\n";
    }
    
    // Also check for existing users
    echo "\n=== All Users ===\n";
    $users = User::all();
    foreach ($users as $user) {
        echo "ID: " . $user->id . " | Email: " . $user->email . " | Name: " . $user->name . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
