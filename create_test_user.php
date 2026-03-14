<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Creating test user for first-time login...\n";

// Check if test user already exists
$existingUser = User::where('email', 'test@test.com')->first();
if ($existingUser) {
    echo "Test user already exists. Updating...\n";
    $existingUser->update([
        'is_first_login' => true,
        'password' => Hash::make('temppass123')
    ]);
    $testUser = $existingUser;
} else {
    $testUser = User::create([
        'name' => 'Test User',
        'email' => 'test@test.com',
        'password' => Hash::make('temppass123'),
        'is_first_login' => true,
        'email_verified_at' => now(),
    ]);
    echo "Test user created successfully!\n";
}

echo "Email: test@test.com\n";
echo "Password: temppass123\n";
echo "First login flag: " . ($testUser->is_first_login ? 'YES' : 'NO') . "\n";
echo "\nThis user will be redirected to change password on first login.\n";

try {
    echo "Creating test user...\n";
    
    $user = User::updateOrCreate(
        ['email' => 'super-admin@savannah.com'],
        [
            'name' => 'Super Admin',
            'email' => 'super-admin@savannah.com',
            'password' => Hash::make('password'),
            'is_first_login' => true,
            'email_verified_at' => now(),
            'is_admin' => 1,
            'user_status' => '1',
            'department_id' => 1
        ]
    );
    
    echo "User created/updated successfully:\n";
    echo "Email: {$user->email}\n";
    echo "Name: {$user->name}\n";
    echo "is_first_login: " . ($user->is_first_login ? 'true' : 'false') . "\n";
    
    // Create a few more test users
    $testUsers = [
        [
            'name' => 'Management User',
            'email' => 'management@savannah.com',
            'password' => Hash::make('password'),
            'is_first_login' => true,
            'email_verified_at' => now(),
            'is_admin' => 1,
            'user_status' => '1',
            'department_id' => 1
        ],
        [
            'name' => 'Field Technician',
            'email' => 'field-technician@savannah.com',
            'password' => Hash::make('password'),
            'is_first_login' => true,
            'email_verified_at' => now(),
            'is_admin' => 0,
            'user_status' => '1',
            'department_id' => 8
        ]
    ];
    
    foreach ($testUsers as $userData) {
        $user = User::updateOrCreate(
            ['email' => $userData['email']],
            $userData
        );
        echo "Created user: {$user->email}\n";
    }
    
    echo "\nTotal users in database: " . User::count() . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
