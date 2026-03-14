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
        Schema::table('outages', function (Blueprint $table) {
            $table->integer('total_customers_affected')->nullable()->after('urgency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outages', function (Blueprint $table) {
            $table->dropColumn('total_customers_affected');
        });
    }
};
