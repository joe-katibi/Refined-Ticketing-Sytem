<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Route;

echo "Creating temporary bypass route...\n";

// Add a temporary route without middleware to test
Route::get('/test-appointment-access', function() {
    return "✓ Appointment access works without middleware!";
});

echo "Temporary route created at: /test-appointment-access\n";
echo "This bypasses all middleware to test basic routing.\n";
