<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if the column exists before trying to modify it
        if (Schema::hasColumn('escalation_histories', 'sub_department_id')) {
            try {
                // Try to drop the foreign key constraint using Laravel's column-based approach
                Schema::table('escalation_histories', function (Blueprint $table) {
                    $table->dropForeign(['sub_department_id']);
                });
            } catch (Exception $e) {
                // If that fails, try to drop by constraint name
                try {
                    DB::statement('ALTER TABLE escalation_histories DROP FOREIGN KEY escalation_histories_sub_department_id_foreign');
                } catch (Exception $e2) {
                    // Foreign key doesn't exist, continue
                }
            }
            
            // Add the correct foreign key constraint
            Schema::table('escalation_histories', function (Blueprint $table) {
                $table->foreign('sub_department_id')
                      ->references('id')
                      ->on('sub_departments')
                      ->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('escalation_histories', 'sub_department_id')) {
            try {
                // Drop the current foreign key constraint
                Schema::table('escalation_histories', function (Blueprint $table) {
                    $table->dropForeign(['sub_department_id']);
                });
            } catch (Exception $e) {
                // Foreign key doesn't exist, continue
            }
            
            // Add back the original foreign key constraint
            Schema::table('escalation_histories', function (Blueprint $table) {
                $table->foreign('sub_department_id')
                      ->references('id')
                      ->on('departments')
                      ->onDelete('set null');
            });
        }
    }
};
