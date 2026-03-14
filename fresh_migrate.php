<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Fresh Database Migration Setup ===\n";

try {
    // Get database configuration
    $database = env('DB_DATABASE');
    $host = env('DB_HOST');
    $username = env('DB_USERNAME');
    $password = env('DB_PASSWORD');
    
    echo "Database: $database\n";
    echo "Host: $host\n";
    echo "Username: $username\n\n";
    
    // Step 1: Drop and recreate database
    echo "Step 1: Dropping and recreating database...\n";
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec("DROP DATABASE IF EXISTS `$database`");
    echo "Database dropped.\n";
    
    $pdo->exec("CREATE DATABASE `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database created.\n";
    $pdo = null;
    
    // Step 2: Run fresh migrations
    echo "\nStep 2: Running fresh migrations...\n";
    Artisan::call('migrate:fresh', ['--force' => true]);
    echo Artisan::output();
    
    // Step 3: Run module migrations
    echo "\nStep 3: Running module migrations...\n";
    Artisan::call('module:migrate', ['--force' => true]);
    echo Artisan::output();
    
    // Step 4: Check migration status
    echo "\nStep 4: Checking migration status...\n";
    Artisan::call('migrate:status');
    echo Artisan::output();
    
    echo "\n=== Fresh migration setup completed successfully! ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
