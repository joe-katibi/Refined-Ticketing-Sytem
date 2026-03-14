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
        Schema::table('appointment_histories', function (Blueprint $table) {
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('appointment_histories', 'assigned_team_id')) {
                $table->unsignedBigInteger('assigned_team_id')->nullable()->after('sub_team_type_id');
            }
            if (!Schema::hasColumn('appointment_histories', 'escalated_team_id')) {
                $table->unsignedBigInteger('escalated_team_id')->nullable()->after('assigned_team_id');
            }
            
            // Add indexes for better performance (only if columns were added)
            if (!Schema::hasColumn('appointment_histories', 'assigned_team_id')) {
                $table->index('assigned_team_id');
            }
            if (!Schema::hasColumn('appointment_histories', 'escalated_team_id')) {
                $table->index('escalated_team_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointment_histories', function (Blueprint $table) {
            $table->dropIndex(['assigned_team_id']);
            $table->dropIndex(['escalated_team_id']);
            $table->dropColumn(['assigned_team_id', 'escalated_team_id']);
        });
    }
};
