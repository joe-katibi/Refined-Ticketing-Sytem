@echo off
echo ===== Running Final Outage Edit Fixes =====
echo.

echo 1. Running new migration for sub_team_type_id...
php artisan migrate --force
if %errorlevel% neq 0 (
    echo Migration failed!
    pause
    exit /b 1
)

echo.
echo 2. Fixing database constraints...
php fix_outage_team_constraint_v2.php
if %errorlevel% neq 0 (
    echo Constraint fix failed!
    pause
    exit /b 1
)

echo.
echo 3. Clearing all caches...
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
composer dump-autoload

echo.
echo ===== All fixes completed successfully! =====
echo.
echo The following issues have been resolved:
echo - Added missing API routes for cascading dropdowns
echo - Fixed OutageNotificationService to use team_types instead of operationalTeams
echo - Added sub_team_type_id column to outages table
echo - Fixed database constraints to reference team_types
echo - Updated controller to handle team assignments correctly
echo.
echo You can now test the outage edit functionality at:
echo http://127.0.0.1:8000/outages/3/edit
echo.
pause
