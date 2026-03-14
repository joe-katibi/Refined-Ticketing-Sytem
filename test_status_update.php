<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Testing direct SQL update for status column...\n\n";

try {
    // Get current status value from lists table
    $list = DB::table('lists')->first();
    if ($list) {
        echo "Current status in lists table: " . $list->status . "\n";
        
        // Try to update with a longer status value using direct SQL
        $affected = DB::update('UPDATE lists SET status = ? WHERE id = ?', ['Scheduled-Open', $list->id]);
        echo "Rows affected in lists table: " . $affected . "\n";
        
        // Verify the update
        $updatedList = DB::table('lists')->where('id', $list->id)->first();
        echo "Updated status in lists table: " . $updatedList->status . "\n";
    } else {
        echo "No records found in lists table.\n";
    }
    
    echo "\n";
    
    // Get current status value from escalations table
    $escalation = DB::table('escalations')->first();
    if ($escalation) {
        echo "Current status in escalations table: " . $escalation->status . "\n";
        
        // Try to update with a longer status value using direct SQL
        $affected = DB::update('UPDATE escalations SET status = ? WHERE id = ?', ['Scheduled-Open', $escalation->id]);
        echo "Rows affected in escalations table: " . $affected . "\n";
        
        // Verify the update
        $updatedEscalation = DB::table('escalations')->where('id', $escalation->id)->first();
        echo "Updated status in escalations table: " . $updatedEscalation->status . "\n";
    } else {
        echo "No records found in escalations table.\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nTest complete.\n";
