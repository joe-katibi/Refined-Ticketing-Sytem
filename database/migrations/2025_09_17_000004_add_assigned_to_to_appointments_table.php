<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Appointments previously only had assigned_team_id (team-level assignment).
 * The FIFO engine assigns individual agents, and Escalations/Outages already
 * have a per-user assigned_to column — this brings Appointments in line so
 * all three modules share the same assignment shape.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('assigned_to')->nullable()->after('assigned_team_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_to');
        });
    }
};
