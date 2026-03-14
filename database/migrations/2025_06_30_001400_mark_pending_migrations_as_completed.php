<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Check if the migrations table exists
        if (Schema::hasTable('migrations')) {
            // Mark the pending migrations as completed
            $migrations = [
                '2025_06_20_183143_create_escalation_history_table',
                '2025_06_22_082700_add_deleted_at_to_appointments_table',
                '2025_06_22_083543_create_appointment_types_table',
                '2025_06_22_092451_create_appointment_final_reasons_table',
            ];

            foreach ($migrations as $migration) {
                // Check if the migration is already in the migrations table
                $exists = DB::table('migrations')
                    ->where('migration', $migration)
                    ->exists();

                // If not, insert it
                if (!$exists) {
                    DB::table('migrations')->insert([
                        'migration' => $migration,
                        'batch' => 1, // Using batch 1 as these are old migrations
                    ]);
                }
            }
        }
    }

    public function down()
    {
        // This is a one-way migration to fix the migrations table
        // No need to implement down() as we're fixing an issue
    }
};
