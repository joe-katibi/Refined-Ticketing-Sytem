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
      Schema::create('olts', function (Blueprint $table) {
        $table->id();
        $table->string('name')->unique(); // e.g., "OLT-Nyali-01"
        $table->string('vendor')->nullable();         // e.g., Huawei, ZTE, FiberHome
        $table->string('model')->nullable();          // e.g., MA5800-X17
        $table->string('ip_address')->unique();  // For SNMP, remote access
        $table->string('location')->nullable();       // Physical location, e.g., cabinet or POP
        $table->integer('total_slots')->nullable();   // Number of slots available
        $table->string('software_version')->nullable(); // Optional
        $table->unsignedBigInteger('created_by')->nullable();
        $table->unsignedBigInteger('edited_by')->nullable();
        $table->string('status')->default('Active');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('olts');
    }
};
