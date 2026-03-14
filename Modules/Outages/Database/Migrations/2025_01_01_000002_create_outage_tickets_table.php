<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutageTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outage_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outage_id')->constrained('outages')->onDelete('cascade');
            $table->string('ticket_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            
            // Status and priority
            $table->enum('status', ['Open', 'In Progress', 'On Hold', 'Resolved', 'Closed'])->default('Open');
            $table->enum('priority', ['Low', 'Medium', 'High', 'Critical'])->default('Medium');
            
            // Impact and urgency
            $table->enum('impact', ['Low', 'Medium', 'High', 'Critical'])->default('Medium');
            $table->enum('urgency', ['Low', 'Medium', 'High', 'Critical'])->default('Medium');
            
            // Timestamps
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            
            // Resolution details
            $table->text('resolution')->nullable();
            $table->text('resolution_notes')->nullable();
            
            // Team and user assignments
            $table->foreignId('assigned_team_id')->nullable()->constrained('operational_teams');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->foreignId('reported_by')->constrained('users');
            
            // SLA tracking
            $table->boolean('sla_breached')->default(false);
            $table->dateTime('sla_breach_time')->nullable();
            
            // Timestamps and soft deletes
            $table->timestamps();
            $table->softDeletes();
            
            // Additional metadata
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            
            // Indexes
            $table->index(['status', 'priority']);
            $table->index('assigned_to');
            $table->index('assigned_team_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('outage_tickets');
    }
}
