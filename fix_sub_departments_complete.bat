@echo off
echo ============================================
echo FIXING SUB DEPARTMENT DROPDOWN ISSUE
echo ============================================

echo.
echo 1. Clearing all Laravel caches...
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan optimize:clear

echo.
echo 2. Refreshing composer autoload...
composer dump-autoload --optimize

echo.
echo 3. Testing database connection and data...
php -r "
require_once 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
echo 'Customer Experience sub departments: ';
echo \App\Models\SubDepartment::where('department_id', 2)->where('sub_department_status', 1)->count();
echo PHP_EOL;
"

echo.
echo 4. Testing API endpoint...
echo Visit this URL after login: http://127.0.0.1:8000/teamtypes/sub-departments?department_id=2

echo.
echo ============================================
echo FIX COMPLETE!
echo ============================================
echo.
echo NEXT STEPS:
echo 1. Apply the JavaScript fix in modal_add_user.blade.php
echo 2. Change: $('#department_id').on('change', function() {
echo 3. To: $('#department_id').on('select2:select', function() {
echo 4. Test the user creation form
echo.
pause
