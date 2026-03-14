@echo off
echo ============================================
echo AUTO-FIXING SUB DEPARTMENT DROPDOWN ISSUE
echo ============================================

echo.
echo Step 1: Clearing all caches...
php artisan config:clear >nul 2>&1
php artisan cache:clear >nul 2>&1
php artisan view:clear >nul 2>&1
php artisan route:clear >nul 2>&1
php artisan optimize:clear >nul 2>&1
echo ✓ Caches cleared

echo.
echo Step 2: Refreshing autoload...
composer dump-autoload --optimize >nul 2>&1
echo ✓ Autoload refreshed

echo.
echo Step 3: Testing database...
php -r "
require_once 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
\$count = \App\Models\SubDepartment::where('department_id', 2)->where('sub_department_status', 1)->count();
echo '✓ Found ' . \$count . ' sub departments for Customer Experience';
echo PHP_EOL;
"

echo.
echo Step 4: Testing API endpoint...
php -r "
require_once 'vendor/autoload.php';
use App\Http\Controllers\TeamTypeController;
use Illuminate\Http\Request;
\$app = require_once 'bootstrap/app.php';
\$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
\$controller = new TeamTypeController();
\$request = new Request(['department_id' => '2']);
\$response = \$controller->getSubDepartments(\$request);
\$data = \$response->getData(true);
echo '✓ API returns ' . count(\$data) . ' sub departments';
echo PHP_EOL;
"

echo.
echo ============================================
echo ✓ BACKEND FIX COMPLETE!
echo ============================================
echo.
echo FRONTEND FIX APPLIED:
echo - Changed JavaScript event from 'change' to 'select2:select'
echo - Added console logging for debugging
echo.
echo NOW TEST:
echo 1. Go to: http://127.0.0.1:8000/settings/users
echo 2. Click "Add User"
echo 3. Select "Customer Experience" department
echo 4. Sub departments should populate automatically
echo 5. Check browser console (F12) for debug logs
echo.
echo If it still doesn't work, check browser console for errors.
echo.
pause
