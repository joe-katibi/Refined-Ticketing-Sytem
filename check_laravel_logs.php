<?php

// Simple script to check Laravel logs for errors related to appointment edit form submission

// Define the log directory
$logDir = __DIR__ . '/storage/logs';

// Check if the directory exists
if (!is_dir($logDir)) {
    echo "ERROR: Log directory not found at: $logDir\n";
    exit(1);
}

// Get all log files
$logFiles = glob($logDir . '/*.log');

if (empty($logFiles)) {
    echo "No log files found in: $logDir\n";
    exit(1);
}

echo "Found " . count($logFiles) . " log files.\n";

// Look for recent errors related to appointments
$appointmentErrors = [];
$appointmentUpdates = [];

foreach ($logFiles as $logFile) {
    echo "Checking log file: " . basename($logFile) . "\n";
    
    // Get the last 1000 lines of the log file (or all if smaller)
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        echo "Could not read file: $logFile\n";
        continue;
    }
    
    $lines = array_slice($lines, max(0, count($lines) - 1000));
    
    foreach ($lines as $line) {
        // Check for appointment-related errors
        if ((strpos($line, 'appointment') !== false || strpos($line, 'Appointment') !== false) && 
            (strpos($line, 'error') !== false || strpos($line, 'Error') !== false || strpos($line, 'exception') !== false || strpos($line, 'Exception') !== false)) {
            $appointmentErrors[] = $line;
        }
        
        // Check for appointment updates
        if ((strpos($line, 'appointment') !== false || strpos($line, 'Appointment') !== false) && 
            (strpos($line, 'update') !== false || strpos($line, 'Update') !== false)) {
            $appointmentUpdates[] = $line;
        }
    }
}

// Display results
echo "\n=== APPOINTMENT ERRORS ===\n";
if (empty($appointmentErrors)) {
    echo "No appointment-related errors found in the logs.\n";
} else {
    echo "Found " . count($appointmentErrors) . " appointment-related errors:\n";
    foreach ($appointmentErrors as $error) {
        echo $error . "\n";
    }
}

echo "\n=== APPOINTMENT UPDATES ===\n";
if (empty($appointmentUpdates)) {
    echo "No appointment update records found in the logs.\n";
} else {
    echo "Found " . count($appointmentUpdates) . " appointment update records:\n";
    foreach ($appointmentUpdates as $update) {
        echo $update . "\n";
    }
}

echo "\nLog check completed.\n";
