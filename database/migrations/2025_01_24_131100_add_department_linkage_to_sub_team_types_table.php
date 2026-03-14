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
        Schema::table('sub_team_types', function (Blueprint $table) {
            // Add department and sub department linkage columns
            if (!Schema::hasColumn('sub_team_types', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable()->after('team_type_id');
                $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('sub_team_types', 'sub_department_id')) {
                $table->unsignedBigInteger('sub_department_id')->nullable()->after('department_id');
                $table->foreign('sub_department_id')->references('id')->on('sub_departments')->onDelete('set null');
            }
            
            // Add indexes for better performance
            if (!Schema::hasIndex('sub_team_types', ['department_id'])) {
                $table->index('department_id');
            }
            
            if (!Schema::hasIndex('sub_team_types', ['sub_department_id'])) {
                $table->index('sub_department_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sub_team_types', function (Blueprint $table) {
            // Drop foreign keys first
            if (Schema::hasColumn('sub_team_types', 'sub_department_id')) {
                $table->dropForeign(['sub_department_id']);
                $table->dropColumn('sub_department_id');
            }
            
            if (Schema::hasColumn('sub_team_types', 'department_id')) {
                $table->dropForeign(['department_id']);
                $table->dropColumn('department_id');
            }
        });
    }
};
