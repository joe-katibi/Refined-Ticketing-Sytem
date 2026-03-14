<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap the application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\Appointment\Models\AppointmentNotification;
use Modules\Appointment\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

echo "Starting Direct Notification Creation Test\n";
echo "---------------------------------------\n\n";

// Find a test user
$user = User::first();
if (!$user) {
    echo "Error: No users found in the database.\n";
    exit(1);
}

// Login as the user
Auth::login($user);
echo "Logged in as: {$user->name} (ID: {$user->id})\n\n";

// Find an existing appointment
$appointment = Appointment::first();
if (!$appointment) {
    echo "Error: No appointments found in the database.\n";
    exit(1);
}

echo "Using appointment with ID: {$appointment->id}\n\n";

// Check if the appointment_notifications table exists
try {
    $tableExists = DB::select("SHOW TABLES LIKE 'appointment_notifications'");
    echo "Table exists check: " . (count($tableExists) > 0 ? "Yes" : "No") . "\n\n";
    
    if (count($tableExists) > 0) {
        // Check table structure
        $columns = DB::select("SHOW COLUMNS FROM appointment_notifications");
        echo "Table columns:\n";
        foreach ($columns as $column) {
            echo "- {$column->Field} ({$column->Type})\n";
        }
        echo "\n";
    }
} catch (\Exception $e) {
    echo "Error checking table: " . $e->getMessage() . "\n\n";
}

// Try to create a notification directly
try {
    echo "Attempting to create notification directly...\n";
    
    $notification = new AppointmentNotification();
    $notification->appointment_id = $appointment->id;
    $notification->user_id = $user->id;
    $notification->message = "Test notification created directly";
    $notification->type = "test";
    $notification->read = false;
    $notification->created_by = $user->id;
    
    $result = $notification->save();
    
    echo "Save result: " . ($result ? "Success" : "Failed") . "\n";
    echo "Notification ID: " . $notification->id . "\n\n";
    
} catch (\Exception $e) {
    echo "Error creating notification: " . $e->getMessage() . "\n\n";
}

// Check if any notifications exist in the database
try {
    $count = AppointmentNotification::count();
    echo "Total notifications in database: {$count}\n\n";
    
    if ($count > 0) {
        $notifications = AppointmentNotification::latest()->take(5)->get();
        echo "Latest 5 notifications:\n";
        foreach ($notifications as $n) {
            echo "- ID: {$n->id}, Message: {$n->message}, Type: {$n->type}\n";
        }
    }
} catch (\Exception $e) {
    echo "Error counting notifications: " . $e->getMessage() . "\n\n";
}

echo "\n---------------------------------------\n";
echo "Test completed.\n";
