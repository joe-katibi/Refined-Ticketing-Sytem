<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Fresh Database Setup ===\n";

try {
    // Get database configuration
    $database = env('DB_DATABASE');
    $host = env('DB_HOST');
    $username = env('DB_USERNAME');
    $password = env('DB_PASSWORD');
    
    echo "Database: $database\n";
    echo "Host: $host\n";
    echo "Username: $username\n\n";
    
    // Connect to MySQL without selecting a database
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to MySQL server successfully.\n";
    
    // Drop the database if it exists
    echo "Dropping database '$database' if it exists...\n";
    $pdo->exec("DROP DATABASE IF EXISTS `$database`");
    echo "Database dropped successfully.\n";
    
    // Create the database
    echo "Creating database '$database'...\n";
    $pdo->exec("CREATE DATABASE `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database created successfully.\n";
    
    // Close the connection
    $pdo = null;
    
    echo "\n=== Database setup completed successfully! ===\n";
    echo "You can now run migrations on the fresh database.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
