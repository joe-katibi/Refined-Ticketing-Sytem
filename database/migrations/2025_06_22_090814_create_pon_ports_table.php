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
      Schema::create('pon_ports', function (Blueprint $table) {
        $table->id();
        $table->foreignId('olt_slot_id')->constrained()->onDelete('cascade');
        $table->integer('pon_port_number')->nullable(); // e.g., 0 to 15
        $table->string('pon_port_type')->nullable(); // GPON, XGSPON, etc.
        $table->string('status')->default('active'); // active, faulty, spare
        $table->unsignedBigInteger('created_by')->nullable();
        $table->unsignedBigInteger('edited_by')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pon_ports');
    }
};
