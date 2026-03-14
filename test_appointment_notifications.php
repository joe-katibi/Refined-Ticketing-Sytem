<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap the application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentNotification;
use Modules\Appointment\Services\NotificationService;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo "Starting Appointment Notification Test\n";
echo "-------------------------------------\n\n";

// Find a test user
$user = User::first();
if (!$user) {
    echo "Error: No users found in the database.\n";
    exit(1);
}

// Login as the user
Auth::login($user);
echo "Logged in as: {$user->name} (ID: {$user->id})\n\n";

// Find an existing appointment or create a new one
$appointment = Appointment::first();
if (!$appointment) {
    echo "No existing appointment found. Creating a new one...\n";
    
    // Create a new appointment
    $appointment = new Appointment();
    $appointment->account_number = 'TEST-' . rand(1000, 9999);
    $appointment->appointment_ticket_id = 'TST-' . rand(100, 999);
    $appointment->priority = 'Medium';
    $appointment->status = 'Scheduled-Open';
    $appointment->created_by = $user->id;
    $appointment->edited_by = $user->id;
    $appointment->save();
    
    echo "Created new appointment with ID: {$appointment->id} and ticket ID: {$appointment->appointment_ticket_id}\n\n";
} else {
    echo "Using existing appointment with ID: {$appointment->id} and ticket ID: {$appointment->appointment_ticket_id}\n\n";
}

// Initialize the notification service
$notificationService = new NotificationService();

// Clear existing notifications for this test
AppointmentNotification::where('appointment_id', $appointment->id)->delete();
echo "Cleared existing notifications for this appointment.\n\n";

// Count notifications before test
$beforeCount = AppointmentNotification::where('appointment_id', $appointment->id)->count();
echo "Notifications before test: {$beforeCount}\n";

// Test creating a notification
echo "Testing notification creation...\n";
$notificationService->notifyAppointmentCreated($appointment);
$afterCreateCount = AppointmentNotification::where('appointment_id', $appointment->id)->count();
echo "Notifications after creation test: {$afterCreateCount}\n";

// Get and display the created notification
$createdNotifications = AppointmentNotification::where('appointment_id', $appointment->id)->get();
if ($createdNotifications->count() > 0) {
    echo "\nCreated notifications:\n";
    foreach ($createdNotifications as $notification) {
        echo "- ID: {$notification->id}, Message: {$notification->message}, Type: {$notification->type}\n";
    }
} else {
    echo "\nWarning: No notifications were created.\n";
}

// Test updating a notification
echo "\nTesting notification update...\n";
$notificationService->notifyAppointmentUpdated($appointment, $user);
$afterUpdateCount = AppointmentNotification::where('appointment_id', $appointment->id)->count();
echo "Notifications after update test: {$afterUpdateCount}\n";

// Get and display all notifications
$allNotifications = AppointmentNotification::where('appointment_id', $appointment->id)->get();
if ($allNotifications->count() > 0) {
    echo "\nAll notifications after tests:\n";
    foreach ($allNotifications as $notification) {
        echo "- ID: {$notification->id}, Message: {$notification->message}, Type: {$notification->type}\n";
    }
} else {
    echo "\nWarning: No notifications were found after tests.\n";
}

echo "\n-------------------------------------\n";
echo "Test completed.\n";
