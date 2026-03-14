<?php

// Simple test script to check if the AppointmentController update method works correctly

// Include autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Create Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Http\Controllers\AppointmentController;

echo "Starting appointment update test...\n";

try {
    // Find an appointment to test with
    $appointment = Appointment::first();
    
    if (!$appointment) {
        echo "No appointments found in database.\n";
        exit(1);
    }
    
    echo "Found appointment ID: " . $appointment->id . "\n";
    echo "Current location: " . $appointment->appointment_location . "\n";
    
    // Create a controller instance
    $controller = new AppointmentController(app()->make('Modules\Appointment\Services\NotificationService'));
    
    // Create a request with test data
    $request = new Illuminate\Http\Request();
    $request->merge([
        'account_number' => $appointment->account_number,
        'appointment_type_id' => $appointment->appointment_type_id,
        'priority' => $appointment->priority,
        'scheduled_date' => date('Y-m-d'),
        'scheduled_time' => date('H:i'),
        'appointment_location' => $appointment->appointment_location . ' (Test Update)',
        'appointment_venue' => $appointment->appointment_venue,
        'description_notes' => $appointment->description_notes,
        'status' => $appointment->status,
        'team_type_id' => $appointment->team_type_id,
        'sub_team_type_id' => $appointment->sub_team_type_id,
        'assigned_team_id' => $appointment->assigned_team_id,
    ]);
    
    // Authenticate a user
    $user = \App\Models\User::first();
    if ($user) {
        echo "Using user: " . $user->name . " (ID: " . $user->id . ")\n";
        auth()->login($user);
    } else {
        echo "Warning: No users found in database. Authentication may fail.\n";
    }
    
    // Call the update method directly
    echo "Calling update method...\n";
    $controller->update($request, $appointment->id);
    
    // Check if the appointment was updated
    $updatedAppointment = Appointment::find($appointment->id);
    echo "Updated location: " . $updatedAppointment->appointment_location . "\n";
    
    if (strpos($updatedAppointment->appointment_location, 'Test Update') !== false) {
        echo "SUCCESS: Appointment was updated successfully!\n";
    } else {
        echo "FAILURE: Appointment was not updated.\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

echo "Test completed.\n";
