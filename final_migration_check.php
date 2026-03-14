<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== Final Migration Status Check ===\n";

try {
    // Get all migration files
    $migrationFiles = collect(glob(database_path('migrations/*.php')))
        ->map(function ($file) {
            return basename($file, '.php');
        });

    // Get completed migrations from database
    $completedMigrations = DB::table('migrations')->pluck('migration');

    // Find pending migrations
    $pendingMigrations = $migrationFiles->diff($completedMigrations);

    echo "Total migration files: " . $migrationFiles->count() . "\n";
    echo "Completed migrations: " . $completedMigrations->count() . "\n";
    echo "Pending migrations: " . $pendingMigrations->count() . "\n\n";

    if ($pendingMigrations->count() > 0) {
        echo "❌ Pending migrations:\n";
        foreach ($pendingMigrations as $migration) {
            echo "  - " . $migration . "\n";
        }
    } else {
        echo "✅ All migrations completed successfully!\n";
    }

    // Check key tables
    $keyTables = ['appointment_types', 'escalations', 'escalation_histories', 'sub_appointment_types'];
    echo "\n=== Key Tables Status ===\n";
    foreach ($keyTables as $table) {
        $exists = Schema::hasTable($table);
        echo $table . ": " . ($exists ? "✅ EXISTS" : "❌ MISSING") . "\n";
    }

    // Check for any failed migrations in the last batch
    $lastBatch = DB::table('migrations')->max('batch');
    if ($lastBatch) {
        echo "\nLast migration batch: " . $lastBatch . "\n";
        $lastMigrations = DB::table('migrations')->where('batch', $lastBatch)->pluck('migration');
        echo "Migrations in last batch: " . $lastMigrations->count() . "\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
