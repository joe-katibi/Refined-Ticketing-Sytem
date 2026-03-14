<?php

// Get database configuration from .env file
$envFile = file_get_contents(__DIR__ . '/.env');
preg_match('/DB_HOST=(.*)/', $envFile, $hostMatches);
preg_match('/DB_PORT=(.*)/', $envFile, $portMatches);
preg_match('/DB_DATABASE=(.*)/', $envFile, $databaseMatches);
preg_match('/DB_USERNAME=(.*)/', $envFile, $usernameMatches);
preg_match('/DB_PASSWORD=(.*)/', $envFile, $passwordMatches);

$host = trim($hostMatches[1] ?? 'localhost');
$port = trim($portMatches[1] ?? '3306');
$database = trim($databaseMatches[1] ?? '');
$username = trim($usernameMatches[1] ?? '');
$password = trim($passwordMatches[1] ?? '');

if (empty($database)) {
    die("Database name not found in .env file\n");
}

try {
    // Connect to the database
    $dsn = "mysql:host=$host;port=$port;dbname=$database";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database: $database\n\n";
    
    // Check current column definition for lists table
    $stmt = $pdo->query("SHOW COLUMNS FROM lists WHERE Field = 'status'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Current lists.status column type: " . ($column['Type'] ?? 'Not found') . "\n";
    
    // Check current column definition for escalations table
    $stmt = $pdo->query("SHOW COLUMNS FROM escalations WHERE Field = 'status'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Current escalations.status column type: " . ($column['Type'] ?? 'Not found') . "\n\n";
    
    // Modify status column in lists table
    echo "Modifying lists.status column to VARCHAR(50)...\n";
    $pdo->exec("ALTER TABLE lists MODIFY COLUMN status VARCHAR(50)");
    
    // Modify status column in escalations table
    echo "Modifying escalations.status column to VARCHAR(50)...\n";
    $pdo->exec("ALTER TABLE escalations MODIFY COLUMN status VARCHAR(50)");
    
    echo "Column modifications completed.\n\n";
    
    // Verify the changes
    $stmt = $pdo->query("SHOW COLUMNS FROM lists WHERE Field = 'status'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Updated lists.status column type: " . ($column['Type'] ?? 'Not found') . "\n";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM escalations WHERE Field = 'status'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Updated escalations.status column type: " . ($column['Type'] ?? 'Not found') . "\n";
    
    echo "\nStatus column fix completed successfully.\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
