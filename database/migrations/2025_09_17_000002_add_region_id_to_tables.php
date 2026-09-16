<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('region_id')->nullable()->after('sub_department_id')->constrained('regions')->nullOnDelete();
        });

        Schema::table('olts', function (Blueprint $table) {
            $table->foreignId('region_id')->nullable()->after('location')->constrained('regions')->nullOnDelete();
        });

        Schema::table('escalations', function (Blueprint $table) {
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
        });

        Schema::table('outages', function (Blueprint $table) {
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        foreach (['users', 'olts', 'escalations', 'appointments', 'outages'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropConstrainedForeignId('region_id');
            });
        }
    }
};
