<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking existing departments and sub departments...\n\n";

// Get all departments
$departments = DB::table('departments')->get();

echo "DEPARTMENTS:\n";
foreach ($departments as $dept) {
  echo "ID: {$dept->id} - Name: {$dept->department_name} - Status: {$dept->department_status}\n";
}

echo "\n" . str_repeat('-', 50) . "\n\n";

// Get all sub departments
$subDepartments = DB::table('sub_departments')->get();

echo "SUB DEPARTMENTS:\n";
if ($subDepartments->isEmpty()) {
  echo "No sub departments found in the database.\n";
} else {
  foreach ($subDepartments as $subDept) {
    $deptName = DB::table('departments')
      ->where('id', $subDept->department_id)
      ->value('department_name');
    echo "ID: {$subDept->id} - Name: {$subDept->sub_department_name} - Department: {$deptName} - Status: {$subDept->sub_department_status}\n";
  }
}

echo "\n" . str_repeat('-', 50) . "\n\n";

// Test the API endpoint logic
echo "TESTING API ENDPOINT LOGIC:\n";
$departmentId = 2; // Customer Experience department ID (assuming it's 2)
echo "Testing for department ID: {$departmentId}\n";

$subDepts = DB::table('sub_departments')
  ->where('department_id', $departmentId)
  ->where('sub_department_status', 1)
  ->get(['id', 'sub_department_name as name']);

echo 'Found ' . $subDepts->count() . " active sub departments for department ID {$departmentId}:\n";
foreach ($subDepts as $subDept) {
  echo "- ID: {$subDept->id}, Name: {$subDept->name}\n";
}
