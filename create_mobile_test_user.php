<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // Create or find the Field-Technician role
    $fieldTechRole = Role::firstOrCreate(['name' => 'Field-Technician']);
    
    // Create test user
    $user = User::updateOrCreate(
        ['email' => 'fieldtech@test.com'],
        [
            'name' => 'Field Technician Test',
            'email' => 'fieldtech@test.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'team_type_id' => 1, // Assuming team type ID 1 exists
        ]
    );
    
    // Create admin user for web login
    $adminUser = User::updateOrCreate(
        ['email' => 'admin@test.com'],
        [
            'name' => 'Administrator',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'team_type_id' => 1,
        ]
    );
    
    // Assign role
    $user->assignRole($fieldTechRole);
    
    echo "✅ Mobile test user created successfully!\n";
    echo "Email: fieldtech@test.com\n";
    echo "Password: password123\n";
    echo "Role: Field-Technician\n";
    
    echo "\n✅ Admin user created successfully!\n";
    echo "Email: admin@test.com\n";
    echo "Password: password\n";
    
    // Create sales test user
    $salesRole = Role::firstOrCreate(['name' => 'Sales-Agent']);
    
    $salesUser = User::updateOrCreate(
        ['email' => 'sales@test.com'],
        [
            'name' => 'Sales Agent Test',
            'email' => 'sales@test.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]
    );
    
    $salesUser->assignRole($salesRole);
    
    echo "✅ Sales test user created successfully!\n";
    echo "Email: sales@test.com\n";
    echo "Password: password123\n";
    echo "Role: Sales-Agent\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
