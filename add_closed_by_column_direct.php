<?php

// Direct database modification script
// This script directly adds the closed_by column to the escalations table

// Include autoloader
require __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Database connection
$host = $_ENV['DB_HOST'];
$port = $_ENV['DB_PORT'];
$database = $_ENV['DB_DATABASE'];
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];

echo "Connecting to database: {$database} on {$host}...\n";

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$database}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected successfully.\n";
    
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM escalations LIKE 'closed_by'");
    $columnExists = (bool)$stmt->fetch();
    
    if (!$columnExists) {
        echo "Adding closed_by column to escalations table...\n";
        
        // Add the column
        $pdo->exec("ALTER TABLE escalations ADD COLUMN closed_by BIGINT UNSIGNED NULL AFTER closed_at");
        echo "Column added successfully.\n";
        
        // Add foreign key constraint
        $pdo->exec("ALTER TABLE escalations ADD CONSTRAINT escalations_closed_by_foreign FOREIGN KEY (closed_by) REFERENCES users(id) ON DELETE SET NULL");
        echo "Foreign key constraint added successfully.\n";
    } else {
        echo "Column closed_by already exists in escalations table.\n";
    }
    
    echo "Script completed successfully.\n";
    
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}
