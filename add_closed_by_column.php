<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Use DB facade to run raw SQL
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    // Check if column already exists
    if (!Schema::hasColumn('escalations', 'closed_by')) {
        // Add the closed_by column
        DB::statement('ALTER TABLE escalations ADD COLUMN closed_by BIGINT UNSIGNED NULL AFTER closed_at');
        
        // Add foreign key constraint
        DB::statement('ALTER TABLE escalations ADD CONSTRAINT escalations_closed_by_foreign FOREIGN KEY (closed_by) REFERENCES users(id) ON DELETE SET NULL');
        
        echo "Successfully added closed_by column to escalations table.\n";
    } else {
        echo "Column closed_by already exists in escalations table.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
