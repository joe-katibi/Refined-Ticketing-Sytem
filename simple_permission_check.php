<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Spatie\Permission\Models\Permission;

// Simple permission check script
echo "Checking menu permissions...\n";

// Menu permissions from verticalMenu.json
$menuPermissions = [
    'view-users-management-menu',
    'view-dashboard-user',
    'view-users-list-menu',
    'view-user-department-menu',
    'view-user-roles-menu',
    'view-user-permissions-menu',
    'view-user-team-types-menu',
    'view-escalation-menu',
    'view-dashboard-escalation',
    'view-escalate-menu',
    'view-escalations-menu',
    'view-escalation-categories-menu',
    'view-escalation-notification-menu',
    'view-escalation-report-menu',
    'view-appointment-menu',
    'view-dashboard-appointment',
    'view-appointments-menu',
    'view-appointment-list-menu',
    'view-assigned-appointments-menu',
    'view-my-appointments-menu',
    'view-appointment-infrastructure-menu',
    'view-appointment-infrastructure-history-menu',
    'view-appointment-infrastructure-new-menu',
    'view-appointment-noc-menu',
    'view-appointment-noc-history-menu',
    'view-appointment-noc-new-menu',
    'view-appointment-type-menu',
    'view-appointment-final-reasons-menu',
    'view-appointment-notification-menu',
    'view-appointment-reports-menu',
    'view-outage-menu',
    'view-dashboard-outage',
    'view-assigned-outage-menu',
    'view-my-outage-menu',
    'view-olt-management-menu',
    'view-affected-areas-menu',
    'view-affected-services-menu',
    'view-outage-final-reasons-menu',
    'view-outage-download-reports-menu'
];

$dbPermissions = Permission::pluck('name')->toArray();
$missingInDb = array_diff($menuPermissions, $dbPermissions);

echo "Menu permissions: " . count($menuPermissions) . "\n";
echo "Database permissions: " . count($dbPermissions) . "\n";
echo "Missing in database: " . count($missingInDb) . "\n\n";

if (!empty($missingInDb)) {
    echo "MISSING PERMISSIONS:\n";
    foreach ($missingInDb as $missing) {
        echo "- {$missing}\n";
    }
} else {
    echo "✓ All menu permissions exist in database!\n";
}
