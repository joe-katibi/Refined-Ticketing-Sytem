<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Same story as reschedule_reason (see the migration that added it):
     * the Appointment model declares `cancelled_reason` fillable and
     * edit_assigned.blade.php has a "Cancelled Reason" textarea bound to
     * it, but the column was never created — updateAssigned() 500'd with
     * "Unknown column 'cancelled_reason'" on any status update at all
     * (not just ones setting status to Cancelled), because the field is
     * unconditionally present in $validatedData once submitted.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->text('cancelled_reason')->nullable()->after('reschedule_reason');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('cancelled_reason');
        });
    }
};
