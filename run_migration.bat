@echo off
echo Running migration to add closed_by column to escalations table...
php artisan migrate --path=database/migrations/2025_08_20_193908_add_closed_by_to_escalations_table.php
echo Migration completed.
pause
