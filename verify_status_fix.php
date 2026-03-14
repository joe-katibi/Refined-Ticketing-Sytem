<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing status column fix...\n\n";

// Test EscalationList (lists table)
$list = \Modules\Escalations\Entities\EscalationList::first();
if ($list) {
    echo "LISTS TABLE TEST:\n";
    echo "Found record with ID: " . $list->id . "\n";
    echo "Current status: " . $list->status . "\n";
    
    try {
        // Try to update with a longer status value
        $list->status = "Scheduled-Open";
        $result = $list->save();
        echo "Update result: " . ($result ? "Success" : "Failed") . "\n";
        echo "New status: " . $list->status . "\n";
    } catch (\Exception $e) {
        echo "Error updating status: " . $e->getMessage() . "\n";
    }
} else {
    echo "No records found in lists table.\n";
}

echo "\n";

// Test Escalation (escalations table)
$escalation = \Modules\Escalations\Entities\Escalation::first();
if ($escalation) {
    echo "ESCALATIONS TABLE TEST:\n";
    echo "Found record with ID: " . $escalation->id . "\n";
    echo "Current status: " . $escalation->status . "\n";
    
    try {
        // Try to update with a longer status value
        $escalation->status = "Scheduled-Open";
        $result = $escalation->save();
        echo "Update result: " . ($result ? "Success" : "Failed") . "\n";
        echo "New status: " . $escalation->status . "\n";
    } catch (\Exception $e) {
        echo "Error updating status: " . $e->getMessage() . "\n";
    }
} else {
    echo "No records found in escalations table.\n";
}

echo "\nStatus column fix verification complete.\n";
