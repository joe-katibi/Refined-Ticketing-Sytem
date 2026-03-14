<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutageAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outage_attachments', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->foreignId('outage_id')->nullable()->constrained('outages')->onDelete('cascade');
            $table->foreignId('ticket_id')->nullable()->constrained('outage_tickets')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            
            // File details
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type');
            $table->unsignedBigInteger('file_size');
            $table->text('description')->nullable();
            
            // Metadata
            $table->foreignId('uploaded_by')->constrained('users');
            
            // Timestamps and soft deletes
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['outage_id', 'ticket_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('outage_attachments');
    }
}
