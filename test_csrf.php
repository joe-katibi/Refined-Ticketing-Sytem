<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Create a request to the login page
$request = Request::create('http://127.0.0.1:8000/login', 'GET');
$response = $kernel->handle($request);

echo "=== CSRF Token Test ===\n";
echo "Status Code: " . $response->getStatusCode() . "\n";

// Check if CSRF token is being generated
if ($response->getStatusCode() === 200) {
    $content = $response->getContent();
    if (strpos($content, '_token') !== false) {
        echo "✅ CSRF token field found in login form\n";
        
        // Extract CSRF token
        preg_match('/<input[^>]*name="_token"[^>]*value="([^"]*)"/', $content, $matches);
        if (!empty($matches[1])) {
            echo "✅ CSRF token value: " . substr($matches[1], 0, 20) . "...\n";
        } else {
            echo "❌ CSRF token value not found\n";
        }
    } else {
        echo "❌ CSRF token field not found in form\n";
    }
} else {
    echo "❌ Login page not accessible\n";
}

// Test session configuration
echo "\n=== Session Configuration ===\n";
echo "Session Driver: " . config('session.driver') . "\n";
echo "Session Domain: " . config('session.domain') . "\n";
echo "Session Secure: " . (config('session.secure') ? 'true' : 'false') . "\n";
echo "APP_URL: " . config('app.url') . "\n";

$kernel->terminate($request, $response);
