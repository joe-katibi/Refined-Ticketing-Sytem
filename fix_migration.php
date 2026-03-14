<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "Checking database state...\n";

try {
    // Check if appointment_types table exists
    if (Schema::hasTable('appointment_types')) {
        echo "appointment_types table exists. Dropping it...\n";
        Schema::dropIfExists('appointment_types');
        echo "Table dropped successfully.\n";
    } else {
        echo "appointment_types table does not exist.\n";
    }
    
    // Check migration status
    $migrations = DB::table('migrations')->where('migration', 'like', '%appointment_types%')->get();
    echo "Found " . count($migrations) . " appointment_types migrations in database:\n";
    foreach ($migrations as $migration) {
        echo "- " . $migration->migration . "\n";
    }
    
    // Remove the old migration record if it exists
    $oldMigration = '2025_06_22_083543_create_appointment_types_table';
    $deleted = DB::table('migrations')->where('migration', $oldMigration)->delete();
    if ($deleted > 0) {
        echo "Removed old migration record: $oldMigration\n";
    }
    
    echo "Database cleanup completed. You can now run 'php artisan migrate'\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
