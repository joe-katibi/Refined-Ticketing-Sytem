<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "=== Complete Login Flow Test ===\n";

// Test actual login POST with credentials
echo "1. Testing login POST with credentials...\n";

$loginData = [
    'email' => 'admin@test.com',
    'password' => 'password',
    '_token' => csrf_token()
];

$request = Request::create('http://127.0.0.1:8000/login', 'POST', $loginData);
$request->headers->set('Content-Type', 'application/x-www-form-urlencoded');

try {
    $response = $kernel->handle($request);
    echo "Login POST status: " . $response->getStatusCode() . "\n";
    echo "Response headers: " . json_encode($response->headers->all()) . "\n";
    
    if ($response->getStatusCode() === 302) {
        $location = $response->headers->get('Location');
        echo "Redirect location: " . $location . "\n";
    }
    
    // Check session
    $session = $request->session();
    if ($session) {
        echo "Session ID: " . $session->getId() . "\n";
        echo "Session data: " . json_encode($session->all()) . "\n";
    }
    
} catch (Exception $e) {
    echo "Login error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

$kernel->terminate($request, $response);
