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
        Schema::table('escalations', function (Blueprint $table) {
            if (!Schema::hasColumn('escalations', 'appointment_id')) {
                $table->unsignedBigInteger('appointment_id')->nullable()->after('ticket_id');
            }
            
            // Only add the foreign key if it doesn't already exist
            $foreignKeys = DB::select(
                "SELECT CONSTRAINT_NAME 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_NAME = 'escalations' 
                AND COLUMN_NAME = 'appointment_id' 
                AND REFERENCED_TABLE_NAME IS NOT NULL"
            );
            
            if (empty($foreignKeys)) {
                $table->foreign('appointment_id')
                      ->references('id')
                      ->on('appointment_types')
                      ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('escalations', function (Blueprint $table) {
            $table->dropForeign(['appointment_id']);
            $table->dropColumn('appointment_id');
        });
    }
};
