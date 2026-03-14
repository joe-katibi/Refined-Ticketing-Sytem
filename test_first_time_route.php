<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

try {
    echo "Testing first-time login route...\n";
    
    // Get the super admin user
    $user = User::where('email', 'super-admin@savannah.com')->first();
    
    if (!$user) {
        echo "User not found!\n";
        exit;
    }
    
    echo "User found: {$user->email}\n";
    echo "is_first_login: " . ($user->is_first_login ? 'true' : 'false') . "\n";
    
    // Simulate authentication
    Auth::login($user);
    echo "User authenticated: " . (Auth::check() ? 'Yes' : 'No') . "\n";
    echo "Auth user is_first_login: " . (Auth::user()->is_first_login ? 'true' : 'false') . "\n";
    
    // Test the controller logic
    $controller = new App\Http\Controllers\Auth\FirstTimePasswordController();
    
    echo "Testing controller show method...\n";
    
    // This should return the view
    $response = $controller->show();
    
    if ($response instanceof Illuminate\Http\RedirectResponse) {
        echo "Redirected to: " . $response->getTargetUrl() . "\n";
    } else {
        echo "View returned successfully\n";
        echo "View name: " . $response->getName() . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
