<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutageActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outage_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outage_id')->constrained('outages')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            
            // Activity details
            $table->enum('activity_type', [
                'created',
                'updated', 
                'status_changed',
                'progress_added',
                'assigned',
                'resolved',
                'closed',
                'reopened',
                'edited',
                'attachment_added',
                'attachment_removed',
                'comment_added',
                'viewed',
                'edit_form_accessed',
                'resolution_updated',
                'final_reason_set'
            ]);
            
            $table->string('activity_title');
            $table->text('activity_description')->nullable();
            
            // Before and after values for tracking changes
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            
            // Status specific fields
            $table->string('old_status')->nullable();
            $table->string('new_status')->nullable();
            
            // Assignment tracking
            $table->foreignId('old_assigned_to')->nullable()->constrained('users');
            $table->foreignId('new_assigned_to')->nullable()->constrained('users');
            $table->foreignId('old_assigned_team_id')->nullable()->constrained('operational_teams');
            $table->foreignId('new_assigned_team_id')->nullable()->constrained('operational_teams');
            
            // Additional metadata
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->boolean('is_system_generated')->default(false);
            $table->boolean('is_major_activity')->default(false);
            
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['outage_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['activity_type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('outage_activities');
    }
}
