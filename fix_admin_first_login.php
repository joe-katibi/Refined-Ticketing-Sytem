<?php

require_once 'vendor/autoload.php';

use App\Models\User;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Fixing Admin First Login Flag ===\n";

try {
    // Update admin user to disable first login requirement
    $admin = User::where('email', 'admin@test.com')->first();
    
    if ($admin) {
        $admin->is_first_login = false;
        $admin->save();
        
        echo "✅ Admin user updated successfully!\n";
        echo "is_first_login flag set to: false\n";
        echo "Admin can now login normally\n";
    } else {
        echo "❌ Admin user not found\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
