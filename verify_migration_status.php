<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "=== Migration Status Check ===\n";

try {
    // Check if appointment_types table exists
    $tableExists = Schema::hasTable('appointment_types');
    echo "appointment_types table exists: " . ($tableExists ? "YES" : "NO") . "\n";
    
    if ($tableExists) {
        // Get table structure
        $columns = Schema::getColumnListing('appointment_types');
        echo "Table columns: " . implode(', ', $columns) . "\n";
    }
    
    // Check migration records
    $migrations = DB::table('migrations')
        ->where('migration', 'like', '%appointment_types%')
        ->get();
    
    echo "\nMigration records for appointment_types:\n";
    if ($migrations->count() > 0) {
        foreach ($migrations as $migration) {
            echo "  - " . $migration->migration . " (batch: " . $migration->batch . ")\n";
        }
    } else {
        echo "  No migration records found\n";
    }
    
    // Check pending migrations
    echo "\nChecking for pending migrations...\n";
    $pendingMigrations = collect(glob(database_path('migrations/*.php')))
        ->map(function ($file) {
            return basename($file, '.php');
        })
        ->reject(function ($migration) {
            return DB::table('migrations')->where('migration', $migration)->exists();
        });
    
    if ($pendingMigrations->count() > 0) {
        echo "Pending migrations:\n";
        foreach ($pendingMigrations as $migration) {
            echo "  - " . $migration . "\n";
        }
    } else {
        echo "No pending migrations\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
