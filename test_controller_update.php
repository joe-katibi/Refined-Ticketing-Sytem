<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Modules\Escalations\Http\Controllers\EscalationsController;
use Modules\Escalations\Entities\Escalation;
use Modules\Escalations\Entities\EscalationList;

// Find a user to authenticate
$user = \App\Models\User::first();
if (!$user) {
    die("No users found in the database.\n");
}

// Authenticate the user
Auth::login($user);
echo "Authenticated as user: " . $user->name . " (ID: " . $user->id . ")\n\n";

// Find an escalation to update
$escalation = Escalation::first();
if (!$escalation) {
    die("No escalations found in the database.\n");
}

echo "Found escalation with ID: " . $escalation->id . "\n";
echo "Current status: " . $escalation->status . "\n\n";

// Create a mock request with the status we want to update to
$request = Request::create('/escalations/' . $escalation->id, 'PUT', [
    'ticket_id' => $escalation->ticket_id,
    'status' => 'Scheduled-Open',
    // Add other required fields from the form
    'account_number' => $escalation->account_number,
    'category_id' => $escalation->category_id,
    'sub_category_id' => $escalation->sub_category_id,
    'sub_department_id' => $escalation->sub_department_id,
    'priority' => $escalation->priority,
    'description' => $escalation->description,
    'escalation_type' => $escalation->escalation_type ?? 'no_appointment'
]);

// Set the authenticated user for the request
$request->setUserResolver(function () use ($user) {
    return $user;
});

try {
    // Create controller instance
    $controller = app()->make(EscalationsController::class);
    
    // Call the update method
    echo "Attempting to update escalation status to 'Scheduled-Open'...\n";
    $response = $controller->update($request, $escalation->id);
    
    echo "Update completed without exceptions.\n\n";
    
    // Check if the update was successful
    $updatedEscalation = Escalation::find($escalation->id);
    echo "Updated escalation status: " . $updatedEscalation->status . "\n";
    
    $escalationList = EscalationList::where('ticket_id', $escalation->ticket_id)->first();
    if ($escalationList) {
        echo "Updated escalation list status: " . $escalationList->status . "\n";
    }
    
    echo "\nStatus update test completed successfully.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
