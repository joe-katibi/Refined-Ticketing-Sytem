<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Spatie\Permission\Models\Permission;

echo "=== AUDITING VERTICAL MENU PERMISSIONS ===\n";

// Read the vertical menu JSON
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

// Get all permissions from database
$dbPermissions = Permission::pluck('name')->toArray();

echo "Menu permissions found: " . count($menuPermissions) . "\n";
echo "Database permissions found: " . count($dbPermissions) . "\n\n";

// Find missing permissions
$missingInDb = array_diff($menuPermissions, $dbPermissions);
$unusedInMenu = array_diff($dbPermissions, $menuPermissions);

echo "=== MISSING IN DATABASE ===\n";
if (empty($missingInDb)) {
    echo "✓ All menu permissions exist in database\n";
} else {
    foreach ($missingInDb as $permission) {
        echo "✗ MISSING: {$permission}\n";
    }
}

echo "\n=== MENU PERMISSIONS AUDIT ===\n";
foreach ($menuPermissions as $permission) {
    $exists = in_array($permission, $dbPermissions);
    $status = $exists ? '✓' : '✗';
    echo "{$status} {$permission}\n";
}

echo "\n=== SUMMARY ===\n";
echo "Total menu permissions: " . count($menuPermissions) . "\n";
echo "Missing in database: " . count($missingInDb) . "\n";
echo "Status: " . (empty($missingInDb) ? 'ALL GOOD' : 'NEEDS FIXES') . "\n";
