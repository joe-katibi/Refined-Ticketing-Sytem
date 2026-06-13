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
        Schema::create('escalation_histories', function (Blueprint $table) {
            $table->id();

            // Reference to the main escalation/ticket
            $table->foreignId('escalation_id')->constrained('escalations')->onDelete('cascade');

            // Reference to the main escalation/ticket
            $table->string('ticket_id')->nullable();
            $table->string('account_number')->nullable();
            $table->integer('category_id')->nullable();
            $table->integer('sub_category_id')->nullable();
            $table->text('description')->nullable();
            $table->string('olt_id')->nullable();
            $table->string('slot_id')->nullable();
            $table->enum('escalation_type', ['no_appointment', 'appointment'])->nullable();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            $table->foreignId('appointment_type_id')->nullable()->constrained('appointment_types')->onDelete('set null');

            // Current status of the escalation at this point in history
            $table->enum('status', ['Created','Scheduled-Open','Scheduled-Closed','Escalated-Open','Escalated-Closed','In-Progress','Completed','Cancelled','Rescheduled']);

            // User who performed the action (assigned, updated status, etc.)
            $table->foreignId('action_by')->nullable()->constrained('users')->onDelete('set null');

            // User who is currently assigned (can be different from action_by)
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');

            // Department/Team this is assigned to (if applicable)
            $table->foreignId('sub_department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->integer('time_spent_minutes')->default(0);
            $table->date('support_date')->nullable();
            $table->time('support_time')->nullable();
            $table->string('support_location')->nullable();
            $table->string('support_venue')->nullable();

            // Priority at this point in time (can change during escalation)
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');

            // Timestamps for tracking when actions were taken
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            // Time tracking
            $table->integer('time_spent_seconds')->default(0)->comment('Time spent in this status in seconds');

            // Reference to the previous history entry (for chaining the escalation path)
            $table->foreignId('previous_history_id')->nullable()->constrained('escalation_histories')->onDelete('set null');

            // Details about the action taken
            $table->text('action_description')->nullable();

            // Any internal notes about this step
            $table->text('internal_notes')->nullable();

            // SLA information
            $table->timestamp('sla_deadline')->nullable();
            $table->boolean('sla_breached')->default(false);

            $table->timestamps();

            // Indexes for better query performance
            $table->index(['escalation_id', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index('sla_deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('escalation_histories');
    }
};
