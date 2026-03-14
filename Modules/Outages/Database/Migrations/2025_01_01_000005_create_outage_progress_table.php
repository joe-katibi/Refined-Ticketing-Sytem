<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutageProgressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outage_progress', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->foreignId('outage_id')->nullable()->constrained('outages')->onDelete('cascade');
            $table->foreignId('ticket_id')->nullable()->constrained('outage_tickets')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            
            // Progress details
            $table->string('status');
            $table->text('notes')->nullable();
            $table->text('action_taken')->nullable();
            $table->text('next_steps')->nullable();
            $table->boolean('is_major_update')->default(false);
            
            // Timestamps and soft deletes
            $table->timestamps();
            $table->softDeletes();
            
            // Additional metadata
            $table->foreignId('created_by')->constrained('users');
            
            // Indexes
            $table->index(['outage_id', 'ticket_id']);
            $table->index('status');
            $table->index('is_major_update');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('outage_progress');
    }
}
