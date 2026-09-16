<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bulk installation-data uploads (site visit records exported from the
     * field tracker) carry customer contact info and site-visit metadata
     * that the appointments table has no columns for.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('customer_name')->nullable()->after('account_number');
            $table->string('contact_number')->nullable()->after('customer_name');
            $table->string('alternative_contact_number')->nullable()->after('contact_number');
            $table->date('date_received')->nullable()->after('alternative_contact_number');
            $table->string('road_name')->nullable()->after('appointment_venue');
            $table->string('dispatcher')->nullable()->after('road_name');
            $table->string('team_assigned_name')->nullable()->after('dispatcher');
            $table->string('fdt_code')->nullable()->after('team_assigned_name');
            $table->string('olt_name_raw')->nullable()->after('fdt_code');
            $table->text('dispatch_update')->nullable()->after('olt_name_raw');
            $table->date('escalation_date')->nullable()->after('dispatch_update');
            $table->string('location_coords')->nullable()->after('escalation_date');
            $table->text('infra_feedback')->nullable()->after('escalation_notes');
            $table->dateTime('infra_feedback_at')->nullable()->after('infra_feedback');
            $table->text('design_feedback')->nullable()->after('infra_feedback_at');
            $table->string('imported_category')->nullable()->after('design_feedback');
            $table->string('imported_status_raw')->nullable()->after('imported_category');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn([
                'customer_name',
                'contact_number',
                'alternative_contact_number',
                'date_received',
                'road_name',
                'dispatcher',
                'team_assigned_name',
                'fdt_code',
                'olt_name_raw',
                'dispatch_update',
                'escalation_date',
                'location_coords',
                'infra_feedback',
                'infra_feedback_at',
                'design_feedback',
                'imported_category',
                'imported_status_raw',
            ]);
        });
    }
};
