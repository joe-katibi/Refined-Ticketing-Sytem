<?php
require __DIR__.'/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\Escalations\App\Models\EscalationHistory;
use Modules\Escalations\Entities\Escalation;
use Modules\Escalations\Entities\EscalationList;

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Set up error handling
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Starting escalation history test...\n";

try {
    // Find a user to authenticate
    $user = \App\Models\User::first();
    if (!$user) {
        throw new Exception("No users found in the database");
    }
    
    // Authenticate the user
    Auth::login($user);
    echo "Authenticated as user: " . $user->name . " (ID: " . $user->id . ")\n";
    
    // Find an existing escalation to work with
    $escalation = Escalation::first();
    if (!$escalation) {
        throw new Exception("No escalations found in the database");
    }
    
    echo "Found escalation with ID: " . $escalation->id . " and ticket ID: " . $escalation->ticket_id . "\n";
    
    // Find the corresponding escalation list
    $escalationList = EscalationList::where('ticket_id', $escalation->ticket_id)->first();
    if (!$escalationList) {
        throw new Exception("No escalation list found for ticket ID: " . $escalation->ticket_id);
    }
    
    echo "Found escalation list with ID: " . $escalationList->id . "\n";
    
    // Create a history record with minimal data
    echo "Creating history record with minimal data...\n";
    $historyData = [
        'escalation_id' => $escalation->id,
        'ticket_id' => $escalation->ticket_id,
        'status' => $escalation->status,
        'action_by' => $user->id,
    ];
    
    $history = EscalationHistory::create($historyData);
    echo "Successfully created history record with ID: " . $history->id . "\n";
    
    // Check the database structure of escalation_histories table
    echo "\nChecking escalation_histories table structure:\n";
    $columns = DB::select("SHOW COLUMNS FROM escalation_histories");
    foreach ($columns as $column) {
        echo "- " . $column->Field . " (" . $column->Type . ")\n";
    }
    
    echo "\nTest completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " on line " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
