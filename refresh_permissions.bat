@echo off
echo Starting permissions refresh...

echo Clearing cache...
php artisan cache:clear
php artisan config:clear

echo Running permissions refresh command...
php artisan permissions:refresh

echo Checking results...
php artisan tinker --execute="echo 'Permissions: ' . \Spatie\Permission\Models\Permission::count() . PHP_EOL; echo 'Roles: ' . \Spatie\Permission\Models\Role::count() . PHP_EOL;"

pause
