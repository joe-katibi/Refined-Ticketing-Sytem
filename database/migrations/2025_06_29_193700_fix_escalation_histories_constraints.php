<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Check if the appointment_type_id column exists
        if (Schema::hasColumn('escalation_histories', 'appointment_type_id')) {
            Schema::table('escalation_histories', function (Blueprint $table) {
                // Get all foreign key constraints for the table
                $foreignKeys = DB::select(
                    "SELECT 
                        CONSTRAINT_NAME,
                        TABLE_NAME,
                        COLUMN_NAME,
                        REFERENCED_TABLE_NAME,
                        REFERENCED_COLUMN_NAME
                    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                    WHERE TABLE_NAME = 'escalation_histories' 
                    AND COLUMN_NAME = 'appointment_type_id' 
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                    AND CONSTRAINT_SCHEMA = DATABASE()"
                );

                // Drop each foreign key constraint
                foreach ($foreignKeys as $fk) {
                    try {
                        // Use the constraint name directly, not as an array
                        $table->dropForeign($fk->CONSTRAINT_NAME);
                    } catch (\Exception $e) {
                        // Log the error but continue
                        \Log::warning("Failed to drop foreign key {$fk->CONSTRAINT_NAME}: " . $e->getMessage());
                    }
                }
                
                // Check if the sub_appointment_types table exists before adding the foreign key
                if (Schema::hasTable('sub_appointment_types')) {
                    // Add the correct foreign key constraint with an explicit name
                    $table->foreign('appointment_type_id', 'fk_escalation_histories_appointment_type')
                        ->references('id')
                        ->on('sub_appointment_types')
                        ->onDelete('set null')
                        ->onUpdate('cascade');
                }
            });
        }
    }

    public function down()
    {
        // This is a one-way migration to fix the constraints
        // No need to implement down() as we're fixing an issue
    }
};
