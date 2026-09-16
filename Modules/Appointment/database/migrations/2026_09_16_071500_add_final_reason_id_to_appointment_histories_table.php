<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * AppointmentController::updateAssigned() has always included
     * final_reason_id in the AppointmentHistory row it writes on every
     * status update, but the column was never created — every single
     * update via the assigned-appointment edit page failed to log its
     * history entry (caught and logged as "Failed to create history
     * record", not surfaced to the user), so the Status History / Timeline
     * silently lost entries for any update that touched a final reason.
     */
    public function up(): void
    {
        Schema::table('appointment_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('final_reason_id')->nullable()->after('appointment_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('appointment_histories', function (Blueprint $table) {
            $table->dropColumn('final_reason_id');
        });
    }
};
