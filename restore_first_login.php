<?php

require_once 'vendor/autoload.php';

use App\Models\User;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Restoring First Login Functionality ===\n";

try {
    // Restore admin user to have first login requirement
    $admin = User::where('email', 'admin@test.com')->first();
    
    if ($admin) {
        $admin->is_first_login = true;
        $admin->save();
        
        echo "✅ Admin user restored to first-time login state\n";
        echo "Email: admin@test.com\n";
        echo "Password: password\n";
        echo "is_first_login: true (will redirect to password setup)\n";
    } else {
        echo "❌ Admin user not found\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
