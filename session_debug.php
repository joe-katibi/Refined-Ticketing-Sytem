<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo "Session & Authentication Debug\n";
echo "=============================\n\n";

// Check if user is logged in via session
session_start();
echo "Session ID: " . session_id() . "\n";
echo "Session data: " . print_r($_SESSION, true) . "\n";

// Check Laravel auth
if (Auth::check()) {
    $user = Auth::user();
    echo "✓ User is authenticated: {$user->name} ({$user->email})\n";
    
    $roles = $user->roles()->pluck('name')->toArray();
    echo "User roles: " . implode(', ', $roles) . "\n";
    
    if ($user->can('view-appointments-menu')) {
        echo "✓ User has 'view-appointments-menu' permission\n";
    } else {
        echo "✗ User does NOT have 'view-appointments-menu' permission\n";
    }
} else {
    echo "✗ No user is currently authenticated\n";
    echo "You need to login first!\n";
}

echo "\nDone!\n";
