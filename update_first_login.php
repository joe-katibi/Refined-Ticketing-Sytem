<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

try {
    echo "Updating all users to have is_first_login = true...\n";
    
    $updated = User::query()->update(['is_first_login' => true]);
    
    echo "Updated {$updated} users successfully.\n";
    
    // Verify the update
    $firstLoginUsers = User::where('is_first_login', true)->count();
    echo "Users with is_first_login = true: {$firstLoginUsers}\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
