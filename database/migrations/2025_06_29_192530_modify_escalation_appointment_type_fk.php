<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Check if the column exists
        if (Schema::hasColumn('escalations', 'appointment_type_id')) {
            try {
                // Try to drop the foreign key constraint using Laravel's column-based approach
                Schema::table('escalations', function (Blueprint $table) {
                    $table->dropForeign(['appointment_type_id']);
                });
            } catch (Exception $e) {
                // If that fails, try to drop by constraint name
                try {
                    DB::statement('ALTER TABLE escalations DROP FOREIGN KEY escalations_appointment_type_id_foreign');
                } catch (Exception $e2) {
                    // Foreign key doesn't exist, continue
                }
            }
            
            // Add the new foreign key constraint to sub_appointment_types
            Schema::table('escalations', function (Blueprint $table) {
                $table->foreign('appointment_type_id')
                      ->references('id')
                      ->on('sub_appointment_types')
                      ->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('escalations', 'appointment_type_id')) {
            try {
                // Drop the current foreign key constraint
                Schema::table('escalations', function (Blueprint $table) {
                    $table->dropForeign(['appointment_type_id']);
                });
            } catch (Exception $e) {
                // Foreign key doesn't exist, continue
            }
            
            // Re-add the old constraint
            Schema::table('escalations', function (Blueprint $table) {
                $table->foreign('appointment_type_id')
                      ->references('id')
                      ->on('appointment_types')
                      ->onDelete('set null');
            });
        }
    }
};
