<?php

// Simple script to add closed_by column to escalations table
// No Laravel bootstrapping, just direct PDO connection

// Database connection parameters - using values from .env file
$envFile = file_get_contents(__DIR__ . '/.env');
preg_match('/DB_CONNECTION=(.*)/', $envFile, $dbConnection);
preg_match('/DB_HOST=(.*)/', $envFile, $dbHost);
preg_match('/DB_PORT=(.*)/', $envFile, $dbPort);
preg_match('/DB_DATABASE=(.*)/', $envFile, $dbName);
preg_match('/DB_USERNAME=(.*)/', $envFile, $dbUsername);
preg_match('/DB_PASSWORD=(.*)/', $envFile, $dbPassword);

$connection = $dbConnection[1] ?? 'mysql';
$host = $dbHost[1] ?? '127.0.0.1';
$port = $dbPort[1] ?? '3306';
$database = $dbName[1] ?? 'laravel';
$username = $dbUsername[1] ?? 'root';
$password = $dbPassword[1] ?? '';

try {
    // Create PDO connection
    $dsn = "{$connection}:host={$host};port={$port};dbname={$database}";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n";
    
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM escalations LIKE 'closed_by'");
    $columnExists = $stmt->fetchColumn();
    
    if (!$columnExists) {
        echo "Adding closed_by column to escalations table...\n";
        
        // Add the column
        $pdo->exec("ALTER TABLE escalations ADD COLUMN closed_by BIGINT UNSIGNED NULL AFTER closed_at");
        
        // Add foreign key constraint
        $pdo->exec("ALTER TABLE escalations ADD CONSTRAINT escalations_closed_by_foreign FOREIGN KEY (closed_by) REFERENCES users(id) ON DELETE SET NULL");
        
        echo "Successfully added closed_by column to escalations table.\n";
    } else {
        echo "Column closed_by already exists in escalations table.\n";
    }
    
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}

echo "Script completed.\n";
