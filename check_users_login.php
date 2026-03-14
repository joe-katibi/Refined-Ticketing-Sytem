<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\User;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "=== User Database Check ===\n";

try {
    $users = User::all();
    echo "Total users found: " . $users->count() . "\n\n";
    
    foreach ($users as $user) {
        echo "ID: " . $user->id . "\n";
        echo "Email: " . $user->email . "\n";
        echo "Name: " . $user->name . "\n";
        echo "Created: " . $user->created_at . "\n";
        echo "---\n";
    }
    
    // Check for admin user
    $admin = User::where('email', 'admin@test.com')->first();
    if ($admin) {
        echo "\n✅ Admin user found: " . $admin->email . "\n";
    } else {
        echo "\n❌ No admin user found\n";
        echo "Creating admin user...\n";
        
        $newAdmin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);
        
        echo "✅ Admin user created: " . $newAdmin->email . " / password\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
