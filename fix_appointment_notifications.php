<?php

// This script adds notification service calls to the AppointmentController
// Run with: php fix_appointment_notifications.php

$controllerPath = __DIR__ . '/Modules/Appointment/Http/Controllers/AppointmentController.php';

if (!file_exists($controllerPath)) {
    echo "Error: AppointmentController.php not found!\n";
    exit(1);
}

$content = file_get_contents($controllerPath);

// Add notification service call to update method
$updatePattern = '/(\s+\/\/ Record history if there are changes.*?}\s+}\s+)(\s+return redirect\(\)->route\(\'appointment\.appointments\.show\', \$appointment->id\)\s+->with\(\'success\', \'Appointment updated successfully\.\'\);)/s';
$updateReplacement = '$1
            // Create notifications using the notification service
            $this->notificationService->notifyAppointmentUpdated($appointment, auth()->user());

        $2';

$content = preg_replace($updatePattern, $updateReplacement, $content);

// Add notification service call to updateAssigned method
$updateAssignedPattern = '/(\s+\/\/ Record history if there are changes.*?}\s+}\s+)(\s+return redirect\(\)->route\(\'appointment\.appointments\.show\', \$appointment->id\)\s+->with\(\'success\', \'Appointment updated successfully\.\'\);)/s';
$updateAssignedReplacement = '$1
            // Create notifications using the notification service
            $this->notificationService->notifyAppointmentUpdated($appointment, auth()->user());

        $2';

$content = preg_replace($updateAssignedPattern, $updateAssignedReplacement, $content);

// Save the updated file
file_put_contents($controllerPath, $content);

echo "AppointmentController.php has been updated successfully!\n";
exit(0);
