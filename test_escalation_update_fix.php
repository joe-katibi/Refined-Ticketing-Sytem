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

echo "Starting escalation update test with fixed controller logic...\n";

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
    
    // Update the escalation status (simulating the controller action)
    $newStatus = 'Scheduled-Open'; // Using a longer status to test the fix
    
    echo "Updating escalation status to: " . $newStatus . "\n";
    
    // Update the escalation
    $escalation->update([
        'status' => $newStatus,
        'edited_by' => $user->id,
    ]);
    
    // Update the escalation list
    $escalationList->update([
        'status' => $newStatus,
        'edited_by' => $user->id,
    ]);
    
    echo "Successfully updated escalation and escalation list status\n";
    
    // Create a history record with our fixed approach
    echo "Creating history record with fixed approach...\n";
    
    // Simulate a request object with only some fields
    $request = new stdClass();
    $request->ticket_id = $escalation->ticket_id;
    $request->status = $newStatus;
    $request->account_number = $escalation->account_number ?? null;
    
    // Build history data array with only available fields
    $historyData = [
        'ticket_id' => $request->ticket_id,
        'status' => $request->status,
        'action_by' => $user->id,
        'escalation_id' => $escalation->id,
    ];
    
    // Only add account_number if it exists
    if (isset($request->account_number)) {
        $historyData['account_number'] = $request->account_number;
    }
    
    // Create the history record
    $history = EscalationHistory::create($historyData);
    echo "Successfully created history record with ID: " . $history->id . "\n";
    
    // Verify the history record was created correctly
    $verifyHistory = EscalationHistory::find($history->id);
    echo "Verified history record:\n";
    echo "- ID: " . $verifyHistory->id . "\n";
    echo "- Ticket ID: " . $verifyHistory->ticket_id . "\n";
    echo "- Status: " . $verifyHistory->status . "\n";
    echo "- Action By: " . $verifyHistory->action_by . "\n";
    
    echo "\nTest completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " on line " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
