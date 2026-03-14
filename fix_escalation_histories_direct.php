<?php
// Direct database fix script for escalation_histories table
// This script adds all missing columns directly using PDO

// Load environment variables from .env file
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Database connection parameters from .env
$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '3306';
$database = getenv('DB_DATABASE') ?: 'ticketing';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

try {
    // Connect to the database
    $dsn = "mysql:host=$host;port=$port;dbname=$database";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n";
    
    // Check if the table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'escalation_histories'");
    if ($stmt->rowCount() == 0) {
        echo "Error: Table 'escalation_histories' does not exist!\n";
        exit(1);
    }
    
    echo "Table 'escalation_histories' exists.\n\n";
    
    // Get existing columns
    $stmt = $pdo->query("SHOW COLUMNS FROM escalation_histories");
    $existingColumns = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $existingColumns[] = $row['Field'];
    }
    
    echo "Existing columns: " . implode(', ', $existingColumns) . "\n\n";
    
    // Define columns to add with their definitions
    $columnsToAdd = [
        'support_date' => 'DATE NULL',
        'support_time' => 'VARCHAR(50) NULL',
        'support_address' => 'VARCHAR(255) NULL',
        'support_notes' => 'TEXT NULL',
        'shifting_date' => 'DATE NULL',
        'shifting_time' => 'VARCHAR(50) NULL',
        'shifting_address' => 'VARCHAR(255) NULL',
        'shifting_notes' => 'TEXT NULL',
        'installation_date' => 'DATE NULL',
        'installation_time' => 'VARCHAR(50) NULL',
        'installation_address' => 'VARCHAR(255) NULL',
        'installation_notes' => 'TEXT NULL',
        'wifi_extender_date' => 'DATE NULL',
        'wifi_extender_time' => 'VARCHAR(50) NULL',
        'wifi_extender_address' => 'VARCHAR(255) NULL',
        'wifi_extender_notes' => 'TEXT NULL'
    ];
    
    // Add missing columns
    $addedColumns = [];
    $skippedColumns = [];
    
    foreach ($columnsToAdd as $column => $definition) {
        if (!in_array($column, $existingColumns)) {
            $sql = "ALTER TABLE escalation_histories ADD COLUMN $column $definition";
            $pdo->exec($sql);
            $addedColumns[] = $column;
            echo "Added column: $column ($definition)\n";
        } else {
            $skippedColumns[] = $column;
        }
    }
    
    echo "\nSummary:\n";
    echo "- Added columns: " . (count($addedColumns) > 0 ? implode(', ', $addedColumns) : "None") . "\n";
    echo "- Skipped columns (already exist): " . (count($skippedColumns) > 0 ? implode(', ', $skippedColumns) : "None") . "\n";
    
    echo "\nFix completed successfully!\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
