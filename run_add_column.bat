@echo off
echo Adding sub_team_type_id column to outages table...
php artisan migrate --force
echo Column added successfully!
pause
