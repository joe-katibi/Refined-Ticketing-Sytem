<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('escalations', function (Blueprint $table) {
            // Check if the column exists
            if (Schema::hasColumn('escalations', 'appointment_type_id')) {
                // Get the current foreign key constraints
                $foreignKeys = DB::select(
                    "SELECT CONSTRAINT_NAME 
                    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                    WHERE TABLE_NAME = 'escalations' 
                    AND COLUMN_NAME = 'appointment_type_id' 
                    AND REFERENCED_TABLE_NAME IS NOT NULL"
                );

                // Drop each foreign key constraint
                foreach ($foreignKeys as $fk) {
                    $table->dropForeign([$fk->CONSTRAINT_NAME]);
                }

                // Add the new foreign key constraint to sub_appointment_types
                $table->foreign('appointment_type_id')
                      ->references('id')
                      ->on('sub_appointment_types')
                      ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('escalations', function (Blueprint $table) {
            // Check if the column exists
            if (Schema::hasColumn('escalations', 'appointment_type_id')) {
                // Get the current foreign key constraints
                $foreignKeys = DB::select(
                    "SELECT CONSTRAINT_NAME 
                    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                    WHERE TABLE_NAME = 'escalations' 
                    AND COLUMN_NAME = 'appointment_type_id' 
                    AND REFERENCED_TABLE_NAME IS NOT NULL"
                );

                // Drop each foreign key constraint
                foreach ($foreignKeys as $fk) {
                    $table->dropForeign([$fk->CONSTRAINT_NAME]);
                }

                // Re-add the old constraint
                $table->foreign('appointment_type_id')
                      ->references('id')
                      ->on('appointment_types')
                      ->onDelete('set null');
            }
        });
    }
};
