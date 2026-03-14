<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

try {
    echo "Checking users in database:\n";
    
    $users = User::all(['email', 'name', 'is_first_login']);
    
    if ($users->count() > 0) {
        foreach ($users as $user) {
            $firstLogin = isset($user->is_first_login) ? ($user->is_first_login ? 'true' : 'false') : 'null';
            echo "- {$user->email} ({$user->name}) - is_first_login: {$firstLogin}\n";
        }
    } else {
        echo "No users found in database.\n";
    }
    
    echo "\nTotal users: " . $users->count() . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
