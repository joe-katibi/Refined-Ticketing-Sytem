@echo off
echo === Fresh Database Setup ===
echo.

echo Step 1: Dropping all tables and running fresh migrations...
php artisan migrate:fresh --force
echo.

echo Step 2: Running module migrations...
php artisan module:migrate --force
echo.

echo Step 3: Checking migration status...
php artisan migrate:status
echo.

echo === Setup completed ===
pause
