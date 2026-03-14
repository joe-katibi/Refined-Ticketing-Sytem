@echo off
echo Running Sub Department Seeder...
php artisan db:seed --class=SubDepartmentSeeder
echo Sub departments seeded successfully!
pause
