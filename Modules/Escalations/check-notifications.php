<?php
/**
 * Simple script to check if notifications are being stored in the database
 * 
 * Usage: php check-notifications.php
 */

// Bootstrap Laravel
require __DIR__ . '/../../bootstrap/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\Escalations\Entities\EscalationNotification;

// Get all notifications
$notifications = EscalationNotification::all();

echo "Found " . $notifications->count() . " notifications in the database:\n\n";

foreach ($notifications as $notification) {
    echo "ID: {$notification->id}\n";
    echo "User ID: {$notification->user_id}\n";
    echo "Escalation ID: {$notification->escalation_id}\n";
    echo "Type: {$notification->type}\n";
    echo "Message: {$notification->message}\n";
    echo "Read: " . ($notification->read ? 'Yes' : 'No') . "\n";
    echo "Created: {$notification->created_at}\n";
    echo "-----------------------------------\n";
}

// Check for notifications for specific user
$userId = 3; // The user ID we used in our test
$userNotifications = EscalationNotification::where('user_id', $userId)->get();

echo "\nFound " . $userNotifications->count() . " notifications for user ID {$userId}:\n\n";

foreach ($userNotifications as $notification) {
    echo "ID: {$notification->id}\n";
    echo "Type: {$notification->type}\n";
    echo "Message: {$notification->message}\n";
    echo "Created: {$notification->created_at}\n";
    echo "-----------------------------------\n";
}
