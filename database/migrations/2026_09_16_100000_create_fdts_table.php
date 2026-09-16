<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Modules\Outages\Models\Fdt, FdtController, and the fdts/fats routes have
// existed since the OLT feature was built, but this table was never
// created — visiting /fdts has always been a 500. Columns match Fdt's
// existing $fillable exactly.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fdts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pon_port_id')->constrained()->onDelete('cascade');
            $table->integer('fdt_number');
            $table->string('fdt_type')->nullable(); // 8-port, 16-port, 24-port, 32-port
            $table->string('location')->nullable();
            $table->integer('capacity')->nullable();
            $table->string('status')->default('active'); // active, inactive, faulty, maintenance
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('edited_by')->nullable();
            $table->timestamps();

            $table->unique(['pon_port_id', 'fdt_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fdts');
    }
};
