<?php
// Test script to verify appointment creation from escalation
// This script will test the fixed appointment ticket ID generation logic

// Load the Laravel application
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use Modules\Escalations\Http\Controllers\EscalationsController;
use Modules\Escalations\Entities\Escalation;
use Modules\Appointment\Entities\Appointment;
use Modules\Appointment\Entities\AppointmentType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Redirect Laravel logs to stdout for this script
Log::listen(function($level, $message, $context) {
    echo "[LOG] {$level}: {$message}\n";
});

// Set up error handling
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "===== TESTING APPOINTMENT CREATION FROM ESCALATION =====\n\n";

try {
    // Find a user to authenticate (preferably a super admin)
    $user = \App\Models\User::whereHas('roles', function($query) {
        $query->where('name', 'super-admin');
    })->first();
    
    // If no super admin found, fall back to any user
    if (!$user) {
        $user = \App\Models\User::first();
    }
    
    if (!$user) {
        throw new Exception("No users found in the database");
    }
    
    // Authenticate the user
    Auth::login($user);
    echo "Authenticated as user: " . $user->name . " (ID: " . $user->id . ")\n";
    echo "User roles: " . implode(', ', $user->getRoleNames()->toArray()) . "\n\n";
    
    // Find an existing escalation to update
    $escalation = Escalation::orderBy('id', 'desc')->first();
    if (!$escalation) {
        throw new Exception("No escalations found in the database");
    }
    echo "Found escalation with ID: " . $escalation->id . " and ticket ID: " . $escalation->ticket_id . "\n";
    echo "Current status: " . $escalation->status . "\n\n";
    
    // Find an appointment type
    $appointmentType = AppointmentType::first();
    if (!$appointmentType) {
        throw new Exception("No appointment types found in the database");
    }
    echo "Found appointment type: " . $appointmentType->type_name . " (ID: " . $appointmentType->id . ")\n\n";
    
    // Get valid category, subcategory and subdepartment IDs
    $category = DB::table('categories')->first();
    $categoryId = $category ? $category->id : 1;
    
    $subCategory = DB::table('sub_categories')->first();
    $subCategoryId = $subCategory ? $subCategory->id : 1;
    
    $subDepartment = DB::table('sub_departments')->first();
    $subDepartmentId = $subDepartment ? $subDepartment->id : 1;
    
    // Create a mock request with all necessary data
    $requestData = [
        'account_number' => '12345678',
        'ticket_id' => $escalation->ticket_id,
        'appointment_id' => $appointmentType->id,
        'appointment_type_id' => $appointmentType->id,
        'description' => 'Test appointment creation from script',
        'priority' => 'high',
        'category_id' => $categoryId,
        'sub_category_id' => $subCategoryId,
        'sub_department_id' => $subDepartmentId,
        'status' => 'pending',
        'support_date' => date('Y-m-d'),
        'support_time' => '14:00',
        'support_address' => '123 Test Street, Test City',
        'support_notes' => 'Test support notes from script'
    ];
    
    echo "Request data prepared:\n";
    echo "- Account Number: {$requestData['account_number']}\n";
    echo "- Ticket ID: {$requestData['ticket_id']}\n";
    echo "- Appointment Type ID: {$requestData['appointment_type_id']}\n";
    echo "- Support Date: {$requestData['support_date']}\n";
    echo "- Support Time: {$requestData['support_time']}\n\n";
    
    $request = Request::create('/escalations/' . $escalation->id, 'PUT', $requestData);
    $request->setUserResolver(function () use ($user) {
        return $user;
    });
    
    // Count appointments before the test
    $beforeCount = Appointment::count();
    echo "Appointments before test: " . $beforeCount . "\n";
    
    // Create a controller instance and call the update method
    $controller = new EscalationsController();
    
    // Start a database transaction so we can rollback after testing
    DB::beginTransaction();
    
    echo "Calling EscalationsController update method...\n";
    echo "This will attempt to create an appointment with the fixed ticket ID generation logic\n";
    $response = $controller->update($request, $escalation);
    
    // Count appointments after the test
    $afterCount = Appointment::count();
    echo "Appointments after test: " . $afterCount . "\n";
    
    // Check if a new appointment was created
    if ($afterCount > $beforeCount) {
        echo "\n✓ Successfully created a new appointment\n";
        
        // Get the latest appointment
        $latestAppointment = Appointment::latest('id')->first();
        echo "New appointment details:\n";
        echo "- ID: " . $latestAppointment->id . "\n";
        echo "- Ticket ID: " . $latestAppointment->appointment_ticket_id . "\n";
        echo "- Escalation Ticket ID: " . $latestAppointment->escalation_ticket_id . "\n";
        echo "- Scheduled Date: " . $latestAppointment->scheduled_date . "\n";
        echo "- Scheduled Time: " . $latestAppointment->scheduled_time . "\n";
        echo "- Location: " . $latestAppointment->appointment_location . "\n";
        
        // Verify appointment_ticket_id is not null
        if ($latestAppointment->appointment_ticket_id) {
            echo "\n✓ SUCCESS: appointment_ticket_id is properly set: " . $latestAppointment->appointment_ticket_id . "\n";
            echo "✓ The fix for the appointment_ticket_id null error is working correctly!\n";
            
            // Check if the ticket ID follows the expected format
            if (preg_match('/^[A-Z]{3}-\d+$/', $latestAppointment->appointment_ticket_id) || 
                preg_match('/^[A-Z]{3}-\d+-\d{3}$/', $latestAppointment->appointment_ticket_id)) {
                echo "✓ Ticket ID format is correct\n";
            } else {
                echo "⚠ Ticket ID format is unusual: " . $latestAppointment->appointment_ticket_id . "\n";
            }
        } else {
            echo "\n✗ ERROR: appointment_ticket_id is NULL!\n";
            echo "✗ The fix for the appointment_ticket_id null error is NOT working!\n";
        }
        
        // Also check if an appointment history record was created
        $appointmentHistory = DB::table('appointment_histories')
            ->where('appointment_id', $latestAppointment->id)
            ->first();
            
        if ($appointmentHistory) {
            echo "\n✓ Appointment history record was created successfully\n";
        } else {
            echo "\n⚠ No appointment history record was created\n";
        }
    } else {
        echo "\n✗ Failed to create a new appointment\n";
        echo "✗ The controller update method did not create an appointment record\n";
    }
    
    // Rollback the transaction to clean up test data
    DB::rollBack();
    echo "\nTest transaction rolled back - no data was permanently changed\n";
    
} catch (Exception $e) {
    // Ensure we rollback if there was an error
    if (DB::transactionLevel() > 0) {
        DB::rollBack();
    }
    echo "\n✗ ERROR OCCURRED DURING TEST\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n";
    
    // Show a more concise stack trace
    $trace = $e->getTrace();
    echo "\nStack trace (simplified):\n";
    foreach (array_slice($trace, 0, 5) as $i => $step) {
        $file = isset($step['file']) ? basename($step['file']) : 'unknown';
        $line = isset($step['line']) ? $step['line'] : '?';
        $function = isset($step['function']) ? $step['function'] : 'unknown';
        $class = isset($step['class']) ? $step['class'] : '';
        $type = isset($step['type']) ? $step['type'] : '';
        
        echo "#{$i} {$file}({$line}): {$class}{$type}{$function}()\n";
    }
}

echo "\n===== TEST COMPLETED =====\n";
