<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateOutageStatusEnum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // First, change the column to a regular string to allow updates
        Schema::table('outages', function (Blueprint $table) {
            $table->string('status', 50)->change();
        });

        // Now update existing status values to match new system
        DB::table('outages')->where('status', 'Reported')->update(['status' => 'support-unconfirmed-outage']);
        DB::table('outages')->where('status', 'In Progress')->update(['status' => 'noc-confirmed-outage']);
        DB::table('outages')->where('status', 'Resolved')->update(['status' => 'infra-resolved']);
        DB::table('outages')->where('status', 'Closed')->update(['status' => 'support-closed']);

        // Now change it back to enum with new values
        Schema::table('outages', function (Blueprint $table) {
            $table->enum('status', [
                'support-unconfirmed-outage',
                'noc-confirmed-outage', 
                'noc-rejected',
                'infra-dispatched',
                'infra-confirmed-outage',
                'infra-resolved',
                'noc-restore-confirmed',
                'noc-incident-outage',
                'support-follow-up',
                'support-closed',
                'auto-monitor-detected',
                'awaiting-customer-confirmation',
                'partial-restore',
                'awaiting-field-access',
                'scheduled-maintenance',
                'vendor-escalated'
            ])->default('support-unconfirmed-outage')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // First change to string to allow updates
        Schema::table('outages', function (Blueprint $table) {
            $table->string('status', 50)->change();
        });

        // Revert status values back to old system
        DB::table('outages')->where('status', 'support-unconfirmed-outage')->update(['status' => 'Reported']);
        DB::table('outages')->where('status', 'noc-confirmed-outage')->update(['status' => 'In Progress']);
        DB::table('outages')->where('status', 'infra-resolved')->update(['status' => 'Resolved']);
        DB::table('outages')->where('status', 'support-closed')->update(['status' => 'Closed']);

        // Change back to original enum
        Schema::table('outages', function (Blueprint $table) {
            $table->enum('status', ['Reported', 'In Progress', 'Resolved', 'Closed'])->default('Reported')->change();
        });
    }
}
