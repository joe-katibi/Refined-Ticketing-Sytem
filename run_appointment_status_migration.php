<?php

// Bootstrap Laravel application
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

echo "Starting appointment status migration and seeding...\n";

try {
    // Run the migration
    echo "Running migration...\n";
    $migrationPath = 'Modules/Appointment/Database/Migrations/2025_09_02_190253_create_appointment_statuses_table.php';
    $exitCode = Artisan::call('migrate', [
        '--path' => $migrationPath,
        '--force' => true,
    ]);
    
    echo "Migration result: " . ($exitCode === 0 ? "Success" : "Failed with code $exitCode") . "\n";
    
    // Run the seeder
    echo "Running seeder...\n";
    $exitCode = Artisan::call('db:seed', [
        '--class' => 'Modules\\Appointment\\Database\\Seeders\\AppointmentStatusSeeder',
        '--force' => true,
    ]);
    
    echo "Seeder result: " . ($exitCode === 0 ? "Success" : "Failed with code $exitCode") . "\n";
    
    echo "Migration and seeding completed.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " on line " . $e->getLine() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
