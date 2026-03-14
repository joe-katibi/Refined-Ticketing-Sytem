// Add this line before the return statement in the update method (around line 365)
$this->notificationService->notifyAppointmentUpdated($appointment, auth()->user());

// Add this line before the return statement in the updateAssigned method (around line 535)
$this->notificationService->notifyAppointmentUpdated($appointment, auth()->user());
