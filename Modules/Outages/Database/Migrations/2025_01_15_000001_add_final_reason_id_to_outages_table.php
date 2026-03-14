<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFinalReasonIdToOutagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('outages', function (Blueprint $table) {
            $table->foreignId('final_reason_id')->nullable()->after('resolution')->constrained('outage_final_reasons');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('outages', function (Blueprint $table) {
            $table->dropForeign(['final_reason_id']);
            $table->dropColumn('final_reason_id');
        });
    }
}
