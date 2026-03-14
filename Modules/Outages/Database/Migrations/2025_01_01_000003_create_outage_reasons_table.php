<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutageReasonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outage_reasons', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->foreignId('outage_id')->nullable()->constrained('outages')->onDelete('cascade');
            $table->foreignId('ticket_id')->nullable()->constrained('outage_tickets')->onDelete('cascade');
            
            // Reason details
            $table->string('reason_type');
            $table->text('description');
            $table->text('root_cause')->nullable();
            $table->text('resolution')->nullable();
            
            // Resolution tracking
            $table->foreignId('resolved_by')->nullable()->constrained('users');
            $table->timestamp('resolved_at')->nullable();
            
            // Timestamps and soft deletes
            $table->timestamps();
            $table->softDeletes();
            
            // Additional metadata
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            
            // Indexes
            $table->index(['outage_id', 'ticket_id']);
            $table->index('resolved_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('outage_reasons');
    }
}
