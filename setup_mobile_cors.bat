@echo off
echo Setting up Mobile CORS Configuration for Laravel Ticketing System...
echo.

echo 1. Clearing Laravel caches...
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo.
echo 2. Optimizing Laravel for production...
php artisan config:cache
php artisan route:cache

echo.
echo 3. Testing CORS endpoints...
echo Testing health endpoint: http://localhost:8000/api/test/health
echo Testing CORS endpoint: http://localhost:8000/api/test/cors
echo Testing mobile appointments: http://localhost:8000/api/mobile/appointments

echo.
echo 4. Starting Laravel development server...
echo Server will be available at: http://localhost:8000
echo Flutter app should connect to: http://localhost:8000/api/mobile/
echo.

echo CORS Configuration Complete!
echo.
echo Next steps:
echo 1. Start Laravel server: php artisan serve --host=localhost --port=8000
echo 2. Test CORS: curl -H "Origin: http://localhost:3000" http://localhost:8000/api/test/cors
echo 3. Your Flutter app should now connect successfully!
echo.

pause
