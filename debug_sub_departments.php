<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\TeamTypeController;
use Illuminate\Http\Request;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DETAILED SUB DEPARTMENT DEBUG ===\n\n";

// 1. Check if SubDepartment model exists and works
echo "1. TESTING SUBDEPARTMENT MODEL:\n";
try {
  $subDeptModel = new \App\Models\SubDepartment();
  echo "✓ SubDepartment model exists\n";

  $count = \App\Models\SubDepartment::count();
  echo "✓ Total sub departments in database: {$count}\n";
} catch (\Exception $e) {
  echo '✗ SubDepartment model error: ' . $e->getMessage() . "\n";
}

echo "\n" . str_repeat('-', 50) . "\n\n";

// 2. Test the exact controller method
echo "2. TESTING CONTROLLER METHOD:\n";
try {
  $controller = new TeamTypeController();

  // Create a mock request with department_id = 2
  $request = new Request(['department_id' => '2']);

  $response = $controller->getSubDepartments($request);
  $responseData = $response->getData(true);

  echo "✓ Controller method executed successfully\n";
  echo '✓ Response status: ' . $response->getStatusCode() . "\n";
  echo '✓ Response data count: ' . count($responseData) . "\n";
  echo "✓ Response data:\n";
  foreach ($responseData as $item) {
    echo "   - ID: {$item['id']}, Name: {$item['name']}\n";
  }
} catch (\Exception $e) {
  echo '✗ Controller method error: ' . $e->getMessage() . "\n";
}

echo "\n" . str_repeat('-', 50) . "\n\n";

// 3. Test the exact database query
echo "3. TESTING DATABASE QUERY:\n";
try {
  $departmentId = 2;
  $subDepartments = \App\Models\SubDepartment::where('department_id', $departmentId)
    ->where('sub_department_status', 1)
    ->get(['id', 'sub_department_name as name']);

  echo "✓ Database query executed successfully\n";
  echo "✓ Found {$subDepartments->count()} active sub departments for department ID {$departmentId}\n";

  foreach ($subDepartments as $subDept) {
    echo "   - ID: {$subDept->id}, Name: {$subDept->name}\n";
  }
} catch (\Exception $e) {
  echo '✗ Database query error: ' . $e->getMessage() . "\n";
}

echo "\n" . str_repeat('-', 50) . "\n\n";

// 4. Check route registration
echo "4. CHECKING ROUTE REGISTRATION:\n";
try {
  $routes = app('router')->getRoutes();
  $found = false;

  foreach ($routes as $route) {
    if (str_contains($route->uri(), 'teamtypes/sub-departments')) {
      echo '✓ Route found: ' . $route->methods()[0] . ' ' . $route->uri() . "\n";
      echo '✓ Route name: ' . $route->getName() . "\n";
      echo '✓ Controller: ' . $route->getActionName() . "\n";
      $found = true;
      break;
    }
  }

  if (!$found) {
    echo "✗ Route not found in registered routes\n";
  }
} catch (\Exception $e) {
  echo '✗ Route check error: ' . $e->getMessage() . "\n";
}

echo "\n" . str_repeat('-', 50) . "\n\n";

// 5. Test different department IDs
echo "5. TESTING ALL DEPARTMENTS:\n";
$departments = DB::table('departments')
  ->where('department_status', 1)
  ->get();

foreach ($departments as $dept) {
  $subDeptCount = \App\Models\SubDepartment::where('department_id', $dept->id)
    ->where('sub_department_status', 1)
    ->count();

  echo "Department: {$dept->department_name} (ID: {$dept->id}) -> {$subDeptCount} active sub departments\n";
}

echo "\n" . str_repeat('-', 50) . "\n\n";

// 6. Check middleware and authentication
echo "6. CHECKING MIDDLEWARE:\n";
echo "Note: This route is behind 'auth' middleware\n";
echo "Make sure you're logged in when testing in browser\n";

echo "\n=== DEBUG COMPLETE ===\n";
echo "\nTo test in browser, visit:\n";
echo "http://127.0.0.1:8000/teamtypes/sub-departments?department_id=2\n";
echo "\nMake sure you're logged in first!\n";
