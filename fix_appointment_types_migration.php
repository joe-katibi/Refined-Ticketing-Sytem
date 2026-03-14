<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "=== Fixing appointment_types migration conflict ===\n";

try {
    // Step 1: Check if table exists
    if (Schema::hasTable('appointment_types')) {
        echo "✓ appointment_types table exists\n";
        
        // Drop the existing table
        Schema::dropIfExists('appointment_types');
        echo "✓ Dropped existing appointment_types table\n";
    } else {
        echo "✓ appointment_types table does not exist\n";
    }
    
    // Step 2: Check migration records
    $migrations = DB::table('migrations')
        ->where('migration', 'like', '%appointment_types%')
        ->get();
    
    echo "Found " . count($migrations) . " appointment_types migration records:\n";
    foreach ($migrations as $migration) {
        echo "  - " . $migration->migration . "\n";
    }
    
    // Step 3: Remove the old/conflicting migration record
    $oldMigration = '2025_06_22_083543_create_appointment_types_table';
    $deleted = DB::table('migrations')->where('migration', $oldMigration)->delete();
    if ($deleted > 0) {
        echo "✓ Removed conflicting migration record: $oldMigration\n";
    }
    
    // Step 4: Also remove any other appointment_types migration records to avoid conflicts
    DB::table('migrations')->where('migration', 'like', '%appointment_types%')->delete();
    echo "✓ Cleared all appointment_types migration records\n";
    
    echo "\n=== Fix completed! Now run: php artisan migrate ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
