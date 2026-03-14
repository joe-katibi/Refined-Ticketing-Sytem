<?php

// Bootstrap Laravel application
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Import necessary classes
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Starting script to add closed_by column...\n";

try {
    // Check if column exists
    $columnExists = Schema::hasColumn('escalations', 'closed_by');
    
    if (!$columnExists) {
        echo "Column does not exist. Adding closed_by column...\n";
        
        // Add the column using raw SQL
        DB::statement('ALTER TABLE escalations ADD COLUMN closed_by BIGINT UNSIGNED NULL AFTER closed_at');
        echo "Column added successfully.\n";
        
        // Add foreign key constraint
        DB::statement('ALTER TABLE escalations ADD CONSTRAINT escalations_closed_by_foreign FOREIGN KEY (closed_by) REFERENCES users(id) ON DELETE SET NULL');
        echo "Foreign key constraint added successfully.\n";
        
        echo "Successfully added closed_by column to escalations table.\n";
    } else {
        echo "Column closed_by already exists in escalations table.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "Script completed.\n";
