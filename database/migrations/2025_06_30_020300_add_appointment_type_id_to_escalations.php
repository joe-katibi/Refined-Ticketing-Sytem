<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('escalations', function (Blueprint $table) {
            if (!Schema::hasColumn('escalations', 'appointment_type_id')) {
                $table->unsignedBigInteger('appointment_type_id')->nullable()->after('appointment_id');
                
                // Add foreign key constraint
                $table->foreign('appointment_type_id')
                    ->references('id')
                    ->on('appointment_types')
                    ->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('escalations', function (Blueprint $table) {
            if (Schema::hasColumn('escalations', 'appointment_type_id')) {
                // Drop foreign key constraint first
                $table->dropForeign(['appointment_type_id']);
                // Then drop the column
                $table->dropColumn('appointment_type_id');
            }
        });
    }
};
