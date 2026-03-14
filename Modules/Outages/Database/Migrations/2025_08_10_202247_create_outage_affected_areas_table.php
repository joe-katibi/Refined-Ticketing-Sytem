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
        Schema::create('outage_affected_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outage_id')->constrained('outages')->onDelete('cascade');
            $table->foreignId('affected_area_id')->constrained('affected_areas')->onDelete('cascade');
            $table->timestamps();
            
            // Ensure unique combinations
            $table->unique(['outage_id', 'affected_area_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outage_affected_areas');
    }
};
