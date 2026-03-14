@echo off
echo Running migration for sub_team_type_id...
php artisan migrate --force
echo Migration completed.
pause
