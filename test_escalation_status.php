<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Find an escalation list record
$escalationList = \Modules\Escalations\Entities\EscalationList::first();

if ($escalationList) {
    echo "Found escalation list with ID: " . $escalationList->id . "\n";
    echo "Current status: " . $escalationList->status . "\n";
    
    // Try to update with a longer status value
    $escalationList->status = "Scheduled-Open";
    
    try {
        $result = $escalationList->save();
        echo "Update result: " . ($result ? "Success" : "Failed") . "\n";
        echo "New status: " . $escalationList->status . "\n";
    } catch (\Exception $e) {
        echo "Error updating status: " . $e->getMessage() . "\n";
    }
} else {
    echo "No escalation list records found.\n";
}
