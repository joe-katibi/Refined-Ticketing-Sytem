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
        if (Schema::hasTable('appointment_histories')) {
            return;
        }

        Schema::create('appointment_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('appointment_id');
            $table->string('escalation_ticket_id')->nullable();
            $table->string('ticket_id')->nullable();
            $table->string('status');
            $table->unsignedBigInteger('action_by');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('sub_department_id')->nullable();
            $table->string('priority')->default('medium');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->integer('time_spent_seconds')->default(0);
            $table->integer('time_spent_minutes')->default(0);
            $table->unsignedBigInteger('previous_history_id')->nullable();
            $table->text('action_description')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamp('sla_deadline')->nullable();
            $table->boolean('sla_breached')->default(false);
            $table->string('account_number')->nullable();
            $table->unsignedBigInteger('sub_category_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('team_type_id')->nullable();
            $table->unsignedBigInteger('sub_team_type_id')->nullable();
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->text('notes_created')->nullable();
            $table->text('notes_closed')->nullable();
            $table->string('closing_reason')->nullable();
            $table->string('escalation_reason')->nullable();
            $table->text('escalation_notes')->nullable();
            $table->string('appointment_type')->nullable();
            $table->string('appointment_status')->nullable();
            $table->string('appointment_location')->nullable();
            $table->string('appointment_venue')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('edited_by')->nullable();

            // Foreign keys - Only adding foreign key for appointments table which is required
            $table->foreign('appointment_id')->references('id')->on('appointments')->onDelete('cascade');

            // Other relationships will be handled at the application level
            $table->index('action_by');
            $table->index('assigned_to');
            $table->index('sub_department_id');
            $table->index('category_id');
            $table->index('sub_category_id');
            $table->string('olt_id', 50)->nullable();
            $table->string('slot_id', 50)->nullable();
            $table->string('action')->nullable();
            $table->unsignedBigInteger('appointment_type_id')->nullable();
            $table->unsignedBigInteger('assigned_team_id')->nullable();
            $table->unsignedBigInteger('escalated_team_id')->nullable();
            $table->date('completed_date')->nullable();
            $table->time('completed_time')->nullable();
            $table->timestamp('edited_at')->nullable();
            $table->json('changes')->nullable();
            $table->text('comment')->nullable();
            $table->date('rescheduled_date')->nullable();
            $table->time('rescheduled_time')->nullable();

            // Indexes
            $table->index('escalation_ticket_id');
            $table->index('ticket_id');
            $table->index('status');
            $table->index('priority');
            $table->index('sla_deadline');
            $table->index('sla_breached');
            $table->index('account_number');
            $table->index('action');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
   public function down(): void
    {
        Schema::dropIfExists('appointment_histories');
    }
};
