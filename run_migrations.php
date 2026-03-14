<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting migration process...\n";

try {
    // Check database connection
    echo "Testing database connection...\n";
    DB::connection()->getPdo();
    echo "Database connection successful.\n";
    
    // Run migrations
    echo "Running migrations...\n";
    Artisan::call('migrate', ['--force' => true]);
    echo Artisan::output();
    
    // Check migration status
    echo "Checking migration status...\n";
    Artisan::call('migrate:status');
    echo Artisan::output();
    
    echo "Migration process completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error during migration: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
