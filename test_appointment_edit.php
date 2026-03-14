<?php

require __DIR__ . '/vendor/autoload.php';

// Load the .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Initialize Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Http\Controllers\AppointmentController;

// Set up logging
Log::info('=== Starting Appointment Edit Test ===');

// Find an existing appointment to test with
try {
    $appointment = Appointment::first();
    
    if (!$appointment) {
        Log::error('No appointments found in the database');
        echo "Error: No appointments found in the database\n";
        exit(1);
    }
    
    Log::info('Found appointment ID: ' . $appointment->id);
    echo "Found appointment ID: " . $appointment->id . "\n";
    
    // Create a test request with valid data
    $requestData = [
        'account_number' => $appointment->account_number,
        'appointment_type_id' => $appointment->appointment_type_id,
        'priority' => $appointment->priority,
        'scheduled_date' => date('Y-m-d'),
        'scheduled_time' => date('H:i'),
        'appointment_location' => $appointment->appointment_location . ' (Updated)',
        'appointment_venue' => $appointment->appointment_venue . ' (Updated)',
        'description_notes' => $appointment->description_notes . ' (Updated)',
        'status' => $appointment->status,
        'team_type_id' => $appointment->team_type_id,
        'sub_team_type_id' => $appointment->sub_team_type_id,
        'assigned_team_id' => $appointment->assigned_team_id,
        '_method' => 'PUT',
        '_token' => csrf_token(),
    ];
    
    Log::info('Created test request data', $requestData);
    
    // Create a request instance
    $request = Request::create(
        '/appointments/' . $appointment->id,
        'PUT',
        $requestData
    );
    
    // Set the authenticated user
    $user = \App\Models\User::first();
    if (!$user) {
        Log::error('No users found in the database');
        echo "Error: No users found in the database\n";
        exit(1);
    }
    
    Log::info('Using user ID: ' . $user->id . ' for authentication');
    auth()->login($user);
    
    // Create controller instance
    $controller = app()->make(AppointmentController::class);
    
    // Call the update method
    Log::info('Calling update method on controller');
    $response = $controller->update($request, $appointment->id);
    
    // Check the response
    Log::info('Response received', ['response' => $response]);
    
    if ($response->getStatusCode() == 302) {
        Log::info('Redirect response received - likely successful');
        echo "Success: Appointment update test passed\n";
    } else {
        Log::warning('Non-redirect response received', ['status' => $response->getStatusCode()]);
        echo "Warning: Unexpected response status: " . $response->getStatusCode() . "\n";
    }
    
    // Verify the appointment was updated
    $updatedAppointment = Appointment::find($appointment->id);
    if ($updatedAppointment->appointment_location == $requestData['appointment_location']) {
        Log::info('Appointment was successfully updated in the database');
        echo "Success: Appointment was updated in the database\n";
    } else {
        Log::warning('Appointment was not updated in the database');
        echo "Warning: Appointment was not updated in the database\n";
    }
    
} catch (\Exception $e) {
    Log::error('Exception occurred: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
    echo "Error: " . $e->getMessage() . "\n";
}

Log::info('=== Appointment Edit Test Complete ===');
