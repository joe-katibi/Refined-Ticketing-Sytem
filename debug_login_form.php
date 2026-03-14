<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "=== Login Form Debug ===\n";

// Test 1: Check if login route exists
echo "1. Checking login routes...\n";
$request = Request::create('http://127.0.0.1:8000/login', 'GET');
$response = $kernel->handle($request);
echo "GET /login status: " . $response->getStatusCode() . "\n";

$request = Request::create('http://127.0.0.1:8000/login', 'POST');
$response = $kernel->handle($request);
echo "POST /login status: " . $response->getStatusCode() . "\n";

// Test 2: Check RouteServiceProvider::HOME
echo "\n2. Checking RouteServiceProvider::HOME...\n";
$homeRoute = \App\Providers\RouteServiceProvider::HOME;
echo "HOME route: " . $homeRoute . "\n";

// Test 3: Check if HOME route exists
try {
    $homeRequest = Request::create('http://127.0.0.1:8000' . $homeRoute, 'GET');
    $homeResponse = $kernel->handle($homeRequest);
    echo "HOME route status: " . $homeResponse->getStatusCode() . "\n";
} catch (Exception $e) {
    echo "HOME route error: " . $e->getMessage() . "\n";
}

// Test 4: Check first-time password route
echo "\n3. Checking first-time password route...\n";
try {
    $firstTimeRequest = Request::create('http://127.0.0.1:8000/password/first-time', 'GET');
    $firstTimeResponse = $kernel->handle($firstTimeRequest);
    echo "First-time password route status: " . $firstTimeResponse->getStatusCode() . "\n";
} catch (Exception $e) {
    echo "First-time password route error: " . $e->getMessage() . "\n";
}

$kernel->terminate($request, $response);
