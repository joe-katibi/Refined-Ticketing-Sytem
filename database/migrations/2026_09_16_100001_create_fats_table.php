<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Same situation as fdts (see 2026_09_16_100000_create_fdts_table.php) —
// Modules\Outages\Models\Fat and FatController already existed with no
// backing table. Columns match Fat's existing $fillable exactly.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fdt_id')->constrained()->onDelete('cascade');
            $table->integer('fat_number');
            $table->string('fat_type')->nullable(); // 4-port, 8-port, 12-port, 16-port
            $table->string('location')->nullable();
            $table->integer('capacity')->nullable();
            $table->string('status')->default('active'); // active, inactive, faulty, maintenance
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('edited_by')->nullable();
            $table->timestamps();

            $table->unique(['fdt_id', 'fat_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fats');
    }
};
