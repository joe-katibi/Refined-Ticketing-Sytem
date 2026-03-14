<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Migration Status Check ===\n";

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
        echo "Pending migrations:\n";
        foreach ($pendingMigrations as $migration) {
            echo "  - " . $migration . "\n";
        }
    } else {
        echo "✅ All migrations completed successfully!\n";
    }

    // Check if appointment_types table exists
    $tables = DB::select("SHOW TABLES LIKE 'appointment_types'");
    echo "\nappointment_types table exists: " . (count($tables) > 0 ? "YES" : "NO") . "\n";

    // Check escalation_histories table
    $tables = DB::select("SHOW TABLES LIKE 'escalation_histories'");
    echo "escalation_histories table exists: " . (count($tables) > 0 ? "YES" : "NO") . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
