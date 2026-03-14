<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get all permissions from database
$dbPermissions = DB::table('permissions')->pluck('name')->toArray();

echo "=== BLADE VIEWS & ROUTES PERMISSIONS AUDIT ===\n\n";

// Define permissions found in Blade views and routes
$bladePermissions = [
    'view-create-user',
    'view-view-user', 
    'view-edit-user',
    'view-edit-user-status',
    'view-btn-create-parameters',
    'view-edit-delete-departments',
    'view-edit-roles-permission'
];

$routePermissions = [
    'view-list-user',
    'view-create-user',
    'view-view-user',
    'view-edit-user',
    'view-edit-user-status'
];

$allFoundPermissions = array_unique(array_merge($bladePermissions, $routePermissions));

echo "=== BLADE VIEW PERMISSIONS ===\n";
foreach ($bladePermissions as $permission) {
    $exists = in_array($permission, $dbPermissions) ? '✅' : '❌';
    echo "$exists $permission\n";
}

echo "\n=== ROUTE PERMISSIONS ===\n";
foreach ($routePermissions as $permission) {
    $exists = in_array($permission, $dbPermissions) ? '✅' : '❌';
    echo "$exists $permission\n";
}

// Check for missing permissions
$missingPermissions = array_diff($allFoundPermissions, $dbPermissions);
if (!empty($missingPermissions)) {
    echo "\n❌ PERMISSIONS NOT FOUND IN DATABASE:\n";
    foreach ($missingPermissions as $permission) {
        echo "  - $permission\n";
    }
} else {
    echo "\n✅ All permissions exist in database\n";
}

// Suggest correct permission names for missing ones
echo "\n=== SUGGESTED CORRECTIONS ===\n";
$corrections = [
    'view-btn-create-parameters' => 'view-user-create-department',
    'view-edit-delete-departments' => 'view-user-edit-department', 
    'view-edit-roles-permission' => 'view-user-edit-role'
];

foreach ($corrections as $wrong => $correct) {
    if (in_array($wrong, $missingPermissions)) {
        echo "❌ $wrong → ✅ $correct\n";
    }
}

echo "\n=== AUDIT COMPLETE ===\n";
