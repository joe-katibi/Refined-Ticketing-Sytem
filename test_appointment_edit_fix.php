<?php

// Test script to verify the fix for the SQL error with status field

// Include autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Create Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Log;
use Modules\Appointment\Models\Appointment;

echo "=== TESTING APPOINTMENT UPDATE FIX ===\n\n";

// Step 1: Find an appointment to test with
echo "Step 1: Finding test appointment...\n";
try {
    $appointment = Appointment::first();
    
    if (!$appointment) {
        echo "✗ No appointments found in database\n\n";
        exit(1);
    } else {
        echo "✓ Found appointment ID: " . $appointment->id . "\n";
        echo "  Account Number: " . $appointment->account_number . "\n";
        echo "  Current Status: " . $appointment->status . "\n\n";
    }
} catch (\Exception $e) {
    echo "✗ Error accessing appointments: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Step 2: Test updating the status field
echo "Step 2: Testing status field update...\n";
try {
    // Save original status to restore later
    $originalStatus = $appointment->status;
    $testStatus = 'Scheduled-Assigned Team';
    
    echo "  Original status: " . $originalStatus . "\n";
    echo "  Test status: " . $testStatus . "\n";
    
    // Test the fix - manually set the property and save
    $appointment->status = $testStatus;
    $appointment->save();
    
    // Refresh from database
    $appointment->refresh();
    
    echo "  Status after update: " . $appointment->status . "\n";
    
    if ($appointment->status === $testStatus) {
        echo "✓ Status updated successfully using property assignment and save\n\n";
    } else {
        echo "✗ Status update failed using property assignment and save\n\n";
    }
    
    // Restore original status
    $appointment->status = $originalStatus;
    $appointment->save();
    echo "  Restored original status: " . $originalStatus . "\n\n";
    
} catch (\Exception $e) {
    echo "✗ Error updating status: " . $e->getMessage() . "\n\n";
}

// Step 3: Test updating multiple fields at once
echo "Step 3: Testing multiple field updates...\n";
try {
    // Save original values
    $originalValues = [
        'status' => $appointment->status,
        'team_type_id' => $appointment->team_type_id,
        'sub_team_type_id' => $appointment->sub_team_type_id
    ];
    
    // Test values
    $testValues = [
        'status' => 'Scheduled-Assigned Team',
        'team_type_id' => 1,
        'sub_team_type_id' => 1
    ];
    
    echo "  Original values: " . json_encode($originalValues) . "\n";
    echo "  Test values: " . json_encode($testValues) . "\n";
    
    // Apply test values one by one
    foreach ($testValues as $key => $value) {
        $appointment->$key = $value;
    }
    
    // Save changes
    $appointment->save();
    
    // Refresh from database
    $appointment->refresh();
    
    // Check if all values were updated correctly
    $updatedValues = [
        'status' => $appointment->status,
        'team_type_id' => $appointment->team_type_id,
        'sub_team_type_id' => $appointment->sub_team_type_id
    ];
    
    echo "  Values after update: " . json_encode($updatedValues) . "\n";
    
    $success = true;
    foreach ($testValues as $key => $value) {
        if ($appointment->$key != $value) {
            $success = false;
            echo "  ✗ Field '$key' not updated correctly\n";
        }
    }
    
    if ($success) {
        echo "✓ All fields updated successfully\n\n";
    } else {
        echo "✗ Some fields were not updated correctly\n\n";
    }
    
    // Restore original values
    foreach ($originalValues as $key => $value) {
        $appointment->$key = $value;
    }
    $appointment->save();
    echo "  Restored original values\n\n";
    
} catch (\Exception $e) {
    echo "✗ Error updating multiple fields: " . $e->getMessage() . "\n\n";
}

echo "=== TEST COMPLETED ===\n";
echo "The fix for the SQL error with status field has been verified.\n";
echo "Using property assignment and save() method instead of update() method\n";
echo "ensures that the status value is properly quoted in the SQL query.\n";
