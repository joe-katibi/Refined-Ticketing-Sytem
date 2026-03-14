<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Create a test request to the appointment route
$request = Illuminate\Http\Request::create('/appointments/appointment', 'GET');

try {
    $response = $kernel->handle($request);
    echo "Status Code: " . $response->getStatusCode() . "\n";
    echo "Content: " . substr($response->getContent(), 0, 200) . "...\n";
    
    if ($response->getStatusCode() === 403) {
        echo "❌ Still getting 403 - middleware is still blocking\n";
    } else {
        echo "✅ Route accessible - status " . $response->getStatusCode() . "\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

$kernel->terminate($request, $response ?? null);
