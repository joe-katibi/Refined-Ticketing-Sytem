<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Corrective migration.
 *
 * 2025_09_02_192838_create_appointment_status_histories_table created this table
 * with only id/timestamps. The follow-up migration
 * (2025_09_02_195000_create_appointment_status_histories_table) that was meant to
 * add the real columns guards on Schema::hasTable() and therefore silently no-ops
 * once the stub table already exists — so appointment_id/previous_status/new_status/
 * notes/changed_by never get created on any environment that ran both migrations in
 * order. This breaks every appointment status write (including creating a new
 * appointment) with "Unknown column 'appointment_id'". This migration adds the
 * missing columns idempotently so it is safe to run on top of the broken state.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointment_status_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('appointment_status_histories', 'appointment_id')) {
                $table->unsignedBigInteger('appointment_id')->after('id');
            }
            if (!Schema::hasColumn('appointment_status_histories', 'previous_status')) {
                $table->string('previous_status')->nullable()->after('appointment_id');
            }
            if (!Schema::hasColumn('appointment_status_histories', 'new_status')) {
                $table->string('new_status')->after('previous_status');
            }
            if (!Schema::hasColumn('appointment_status_histories', 'notes')) {
                $table->text('notes')->nullable()->after('new_status');
            }
            if (!Schema::hasColumn('appointment_status_histories', 'changed_by')) {
                $table->unsignedBigInteger('changed_by')->nullable()->after('notes');
            }
        });

        $existingForeignKeys = collect(\Illuminate\Support\Facades\DB::select(
            'SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = ?
               AND TABLE_NAME = ?
               AND CONSTRAINT_TYPE = ?',
            [Schema::getConnection()->getDatabaseName(), 'appointment_status_histories', 'FOREIGN KEY']
        ))->pluck('CONSTRAINT_NAME')->toArray();

        Schema::table('appointment_status_histories', function (Blueprint $table) use ($existingForeignKeys) {
            if (!in_array('appointment_status_histories_appointment_id_foreign', $existingForeignKeys)) {
                $table->foreign('appointment_id')->references('id')->on('appointments')->onDelete('cascade');
            }
            if (!in_array('appointment_status_histories_changed_by_foreign', $existingForeignKeys)) {
                $table->foreign('changed_by')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointment_status_histories', function (Blueprint $table) {
            $table->dropForeign(['appointment_id']);
            $table->dropForeign(['changed_by']);
            $table->dropColumn(['appointment_id', 'previous_status', 'new_status', 'notes', 'changed_by']);
        });
    }
};
