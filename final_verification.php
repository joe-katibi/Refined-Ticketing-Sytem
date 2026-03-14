<?php
// Final verification script for all escalation fixes
// This script will verify:
// 1. Status column length in lists and escalations tables
// 2. Presence of all required columns in escalation_histories table
// 3. Ability to create escalation history records with appointment fields

// Load the Laravel application
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Set up error handling
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connect directly to the database using PDO for more reliable output
try {
    // Get database connection details from Laravel config
    $host = config('database.connections.mysql.host');
    $port = config('database.connections.mysql.port');
    $database = config('database.connections.mysql.database');
    $username = config('database.connections.mysql.username');
    $password = config('database.connections.mysql.password');
    
    // Connect to the database
    $dsn = "mysql:host=$host;port=$port;dbname=$database";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "===== FINAL VERIFICATION OF ESCALATION FIXES =====\n\n";
    
    // PART 1: Verify status column length in lists and escalations tables
    echo "PART 1: STATUS COLUMN LENGTH VERIFICATION\n";
    echo "----------------------------------------\n";
    
    // Check lists table
    $stmt = $pdo->query("SHOW COLUMNS FROM lists WHERE Field = 'status'");
    $listsStatusColumn = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$listsStatusColumn) {
        echo "ERROR: Status column not found in lists table!\n";
    } else {
        $listsStatusType = $listsStatusColumn['Type'];
        echo "Lists table status column type: " . $listsStatusType . "\n";
        
        // Check if the length is at least 50
        if (preg_match('/varchar\((\d+)\)/i', $listsStatusType, $matches)) {
            $length = (int)$matches[1];
            echo "Lists table status column length: " . $length . " characters\n";
            if ($length >= 50) {
                echo "✓ Lists table status column length is sufficient (>= 50 characters)\n";
            } else {
                echo "✗ Lists table status column length is insufficient (< 50 characters)\n";
            }
        }
    }
    
    // Check escalations table
    $stmt = $pdo->query("SHOW COLUMNS FROM escalations WHERE Field = 'status'");
    $escalationsStatusColumn = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$escalationsStatusColumn) {
        echo "ERROR: Status column not found in escalations table!\n";
    } else {
        $escalationsStatusType = $escalationsStatusColumn['Type'];
        echo "Escalations table status column type: " . $escalationsStatusType . "\n";
        
        // Check if the length is at least 50
        if (preg_match('/varchar\((\d+)\)/i', $escalationsStatusType, $matches)) {
            $length = (int)$matches[1];
            echo "Escalations table status column length: " . $length . " characters\n";
            if ($length >= 50) {
                echo "✓ Escalations table status column length is sufficient (>= 50 characters)\n";
            } else {
                echo "✗ Escalations table status column length is insufficient (< 50 characters)\n";
            }
        }
    }
    
    echo "\n";
    
    // PART 2: Verify closed_by column in escalations table
    echo "PART 2: CLOSED_BY COLUMN VERIFICATION\n";
    echo "-----------------------------------\n";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM escalations WHERE Field = 'closed_by'");
    $closedByColumn = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$closedByColumn) {
        echo "✗ closed_by column not found in escalations table!\n";
    } else {
        echo "✓ closed_by column exists in escalations table\n";
        echo "  - Type: " . $closedByColumn['Type'] . "\n";
        echo "  - Nullable: " . ($closedByColumn['Null'] === 'YES' ? 'Yes' : 'No') . "\n";
    }
    
    echo "\n";
    
    // PART 3: Verify escalation_histories table structure
    echo "PART 3: ESCALATION_HISTORIES TABLE VERIFICATION\n";
    echo "--------------------------------------------\n";
    
    // Check if the table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'escalation_histories'");
    if ($stmt->rowCount() == 0) {
        echo "ERROR: Table 'escalation_histories' does not exist!\n";
    } else {
        echo "✓ Table 'escalation_histories' exists\n";
        
        // Get all columns
        $stmt = $pdo->query("SHOW COLUMNS FROM escalation_histories");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $columnNames = array_column($columns, 'Field');
        
        echo "Total columns in escalation_histories table: " . count($columnNames) . "\n\n";
        
        // Check for required columns
        $requiredColumns = [
            'support_address', 'support_date', 'support_time', 'support_notes',
            'shifting_address', 'shifting_date', 'shifting_time', 'shifting_notes',
            'installation_address', 'installation_date', 'installation_time', 'installation_notes',
            'wifi_extender_address', 'wifi_extender_date', 'wifi_extender_time', 'wifi_extender_notes'
        ];
        
        echo "Checking for required appointment-related columns:\n";
        $allFound = true;
        foreach ($requiredColumns as $column) {
            if (in_array($column, $columnNames)) {
                echo "✓ " . str_pad($column, 25) . " - Found\n";
            } else {
                echo "✗ " . str_pad($column, 25) . " - Missing\n";
                $allFound = false;
            }
        }
        
        if ($allFound) {
            echo "\n✓ All required columns exist in escalation_histories table\n";
        } else {
            echo "\n✗ Some required columns are missing from escalation_histories table\n";
        }
    }
    
    echo "\n";
    
    // PART 4: Test inserting a record with appointment fields
    echo "PART 4: TESTING RECORD INSERTION WITH APPOINTMENT FIELDS\n";
    echo "-----------------------------------------------------\n";
    
    try {
        // Find an existing escalation to reference
        $stmt = $pdo->query("SELECT id, ticket_id FROM escalations LIMIT 1");
        $escalation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$escalation) {
            echo "ERROR: No escalations found in the database to reference\n";
        } else {
            echo "Found escalation with ID: " . $escalation['id'] . " and ticket ID: " . $escalation['ticket_id'] . "\n";
            
            // Insert a test record directly with PDO
            $sql = "INSERT INTO escalation_histories (
                escalation_id, ticket_id, status, action_by, 
                support_date, support_time, support_address, support_notes,
                created_at, updated_at
            ) VALUES (
                :escalation_id, :ticket_id, :status, :action_by,
                :support_date, :support_time, :support_address, :support_notes,
                NOW(), NOW()
            )";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'escalation_id' => $escalation['id'],
                'ticket_id' => $escalation['ticket_id'],
                'status' => 'in_progress',
                'action_by' => 1, // Assuming user ID 1 exists
                'support_date' => '2025-08-22',
                'support_time' => '15:30',
                'support_address' => '123 Test Street, Test City',
                'support_notes' => 'Test support notes for verification'
            ]);
            
            $historyId = $pdo->lastInsertId();
            echo "✓ Successfully inserted history record with ID: " . $historyId . "\n";
            
            // Verify the record was inserted correctly
            $stmt = $pdo->prepare("SELECT * FROM escalation_histories WHERE id = :id");
            $stmt->execute(['id' => $historyId]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($record) {
                echo "✓ Record retrieved successfully\n";
                echo "  - Support date: " . $record['support_date'] . "\n";
                echo "  - Support time: " . $record['support_time'] . "\n";
                echo "  - Support address: " . $record['support_address'] . "\n";
                echo "  - Support notes: " . $record['support_notes'] . "\n";
            } else {
                echo "✗ Failed to retrieve inserted record\n";
            }
        }
    } catch (PDOException $e) {
        echo "✗ Error inserting test record: " . $e->getMessage() . "\n";
    }
    
    echo "\n===== VERIFICATION COMPLETED =====\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
