<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Set up error handling
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Checking escalation_histories table structure...\n\n";

try {
    // Check if the table exists
    $tableExists = DB::select("SHOW TABLES LIKE 'escalation_histories'");
    if (empty($tableExists)) {
        echo "Table 'escalation_histories' does not exist!\n";
        exit(1);
    }
    
    echo "Table 'escalation_histories' exists.\n\n";
    
    // Get table columns
    echo "Columns in escalation_histories table:\n";
    $columns = DB::select("SHOW COLUMNS FROM escalation_histories");
    foreach ($columns as $column) {
        echo "- " . str_pad($column->Field, 25) . " | " . $column->Type . "\n";
    }
    
    // Check if support_address column exists
    $supportAddressExists = false;
    foreach ($columns as $column) {
        if ($column->Field === 'support_address') {
            $supportAddressExists = true;
            break;
        }
    }
    
    if ($supportAddressExists) {
        echo "\nsupport_address column exists in the table.\n";
    } else {
        echo "\nsupport_address column DOES NOT exist in the table!\n";
        echo "We need to create a migration to add this column.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
