<?php

require_once __DIR__ . '/vendor/autoload.php';

use Spatie\Permission\Models\Permission;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== CHECKING MENU PERMISSIONS ===\n";

// Read menu JSON
$menuJson = file_get_contents(__DIR__ . '/resources/menu/verticalMenu.json');
$menuData = json_decode($menuJson, true);

// Extract all permissions from menu
$menuPermissions = [];

function extractPermissions($items, &$permissions) {
    foreach ($items as $item) {
        if (isset($item['permission'])) {
            $permissions[] = $item['permission'];
        }
        if (isset($item['submenu'])) {
            extractPermissions($item['submenu'], $permissions);
        }
    }
}

extractPermissions($menuData['menu'], $menuPermissions);
$menuPermissions = array_unique($menuPermissions);

// Get database permissions
$dbPermissions = Permission::pluck('name')->toArray();

// Find missing permissions
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

echo "\n=== ALL MENU PERMISSIONS ===\n";
foreach ($menuPermissions as $permission) {
    $exists = in_array($permission, $dbPermissions);
    $status = $exists ? '✓' : '✗';
    echo "{$status} {$permission}\n";
}
echo "\n=== AUDIT COMPLETE ===\n";
