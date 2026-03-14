<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Permission;
use Spatie\Permission\Models\Role;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ROLE PERMISSIONS AUDIT ===\n\n";

// Get all permissions that exist in database
$existingPermissions = Permission::pluck('name')->toArray();
echo "Total permissions in database: " . count($existingPermissions) . "\n\n";

// Define role permissions from RolesSeeder
$rolePermissions = [
    'Sales-Agent' => [
        'view-escalate-dashboard-menu','view-escalation-menu','view-create-escalation','view-edit-escalation','view-view-escalation','view-history-escalation','view-escalation-notification-mark-read',
        'view-dashboard-appointment-menu','view-appointments-menu','view-appointment-infrastructure-menu','view-appointment-infrastructure-history-menu','view-appointment-noc-menu','view-appointment-noc-history-menu',
        'view-appointment-infrastructure-history-view','view-appointment-noc-history-view','view-appointment-notification-menu','view-outage-dashboard-menu','view-outage-menu'
    ],
    'Team-Leader-Sales' => [
        'view-escalate-dashboard-menu','view-escalation-menu','view-escalations-menu','view-create-escalation','view-edit-escalation','view-view-escalation','view-history-escalation','view-escalation-notification-mark-read',
        'view-history-escalation','view-handle-edit-escalation','view-dashboard-appointment-menu','view-appointments-menu','view-appointment-infrastructure-menu','view-appointment-infrastructure-history-menu',
        'view-appointment-noc-menu','view-appointment-noc-history-menu','view-appointment-infrastructure-history-view','view-appointment-noc-history-view','view-appointment-notification-menu','view-outage-dashboard-menu','view-outage-menu','view-outage-notification-menu','view-outage-notification-mark-read'
    ],
    'Call-Center-Agent' => [
        'view-escalate-dashboard-menu','view-escalation-menu','view-create-escalation','view-edit-escalation','view-view-escalation','view-history-escalation','view-escalation-notification-mark-read',
        'view-dashboard-appointment-menu','view-appointments-menu','view-appointment-infrastructure-menu','view-appointment-infrastructure-history-menu','view-appointment-noc-menu','view-appointment-noc-history-menu',
        'view-appointment-infrastructure-history-view','view-appointment-noc-history-view','view-appointment-notification-menu','view-outage-dashboard-menu','view-outage-menu','view-outage-notification-menu','view-outage-notification-mark-read'
    ]
];

// Check each role for missing permissions
foreach ($rolePermissions as $roleName => $permissions) {
    echo "=== $roleName ===\n";
    $missingPermissions = [];
    $validPermissions = [];
    
    foreach ($permissions as $permission) {
        if (!in_array($permission, $existingPermissions)) {
            $missingPermissions[] = $permission;
        } else {
            $validPermissions[] = $permission;
        }
    }
    
    echo "Valid permissions: " . count($validPermissions) . "\n";
    echo "Missing permissions: " . count($missingPermissions) . "\n";
    
    if (!empty($missingPermissions)) {
        echo "MISSING:\n";
        foreach ($missingPermissions as $missing) {
            echo "  - $missing\n";
        }
    }
    echo "\n";
}

// Check for common permission name patterns that might be wrong
echo "=== PERMISSION NAME ANALYSIS ===\n";
$commonMismatches = [
    'view-escalate-dashboard-menu' => 'view-dashboard-escalation',
    'view-dashboard-appointment-menu' => 'view-dashboard-appointment',
    'view-outage-dashboard-menu' => 'view-dashboard-outage'
];

foreach ($commonMismatches as $wrong => $correct) {
    $wrongExists = in_array($wrong, $existingPermissions);
    $correctExists = in_array($correct, $existingPermissions);
    
    echo "Wrong: '$wrong' exists: " . ($wrongExists ? 'YES' : 'NO') . "\n";
    echo "Correct: '$correct' exists: " . ($correctExists ? 'YES' : 'NO') . "\n";
    echo "---\n";
}
