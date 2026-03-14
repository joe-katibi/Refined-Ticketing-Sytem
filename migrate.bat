@echo off
echo Starting migration process...

echo Testing database connection...
php artisan tinker --execute="try { DB::connection()->getPdo(); echo 'Database connection: OK'; } catch (Exception $e) { echo 'Database error: ' . $e->getMessage(); }"

echo.
echo Running migrations...
php artisan migrate --force

echo.
echo Checking migration status...
php artisan migrate:status

echo.
echo Migration process completed.
pause
