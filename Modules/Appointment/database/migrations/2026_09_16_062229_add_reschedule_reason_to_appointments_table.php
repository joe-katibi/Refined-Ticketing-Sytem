<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The Appointment model has always declared `reschedule_reason` fillable
     * and the edit-assigned form has always had a "Reschedule Reason"
     * textarea bound to it, but no migration ever created this column —
     * AppointmentController::updateAssigned() had to explicitly strip it out
     * of every request before saving to avoid an "Unknown column" SQL error,
     * silently discarding whatever the dispatcher/technician typed there.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->text('reschedule_reason')->nullable()->after('rescheduled_time');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('reschedule_reason');
        });
    }
};
