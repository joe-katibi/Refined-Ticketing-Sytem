<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if the table exists and has the expected columns
        if (Schema::hasTable('escalation_histories')) {
            Schema::table('escalation_histories', function (Blueprint $table) {
                // Add any missing columns or modify existing ones as needed
                // This is just an example - adjust based on your actual table structure
                if (!Schema::hasColumn('escalation_histories', 'appointment_type_id')) {
                    $table->unsignedBigInteger('appointment_type_id')->nullable()->after('appointment_id');
                    $table->foreign('appointment_type_id')
                          ->references('id')
                          ->on('appointment_types')
                          ->onDelete('set null');
                }
                
                // Add other columns as needed
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a one-way migration to fix the table structure
        // No need to implement down() as we're fixing an issue
    }
};
