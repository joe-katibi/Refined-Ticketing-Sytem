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
        Schema::create('ticket_photos', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_id')->index(); // Universal ticket ID
            $table->enum('ticket_type', ['appointment', 'outage'])->index(); // Module type
            $table->unsignedBigInteger('appointment_id')->nullable()->index(); // Link to appointments table
            $table->unsignedBigInteger('outage_id')->nullable()->index(); // Link to outages table
            
            // Appointment Module Photos
            $table->string('onu_photo')->nullable(); // ONU Photo
            $table->string('atb_photo')->nullable(); // ATB Photo  
            $table->string('speed_test_photo')->nullable(); // Speed test picture
            
            // Outage Module Photos
            $table->string('fdt_photo')->nullable(); // FDT Photo
            $table->string('mdu_photo')->nullable(); // MDU Photo
            $table->string('pre_outage_photo')->nullable(); // Pre-outage Photo
            $table->string('post_outage_photo')->nullable(); // Post-outage Photo
            
            // Common fields
            $table->string('uploaded_by')->nullable(); // User who uploaded
            $table->text('notes')->nullable(); // Optional notes for photos
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('appointment_id')->references('id')->on('appointments')->onDelete('cascade');
            $table->foreign('outage_id')->references('id')->on('outages')->onDelete('cascade');
            
            // Indexes for better performance
            $table->index(['ticket_type', 'ticket_id']);
            $table->index(['appointment_id', 'ticket_type']);
            $table->index(['outage_id', 'ticket_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_photos');
    }
};
