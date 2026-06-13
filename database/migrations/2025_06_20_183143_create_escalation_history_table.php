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
            $table->unsignedBigInteger('escalation_id');
            $table->string('ticket_id')->nullable();
            $table->string('account_number')->nullable();
            $table->integer('category_id')->nullable();
            $table->integer('sub_category_id')->nullable();
            $table->text('description')->nullable();
            $table->string('olt_id')->nullable();
            $table->string('slot_id')->nullable();
            $table->enum('escalation_type', ['no_appointment', 'appointment'])->nullable();
            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->unsignedBigInteger('appointment_type_id')->nullable();
            $table->enum('status', ['Created','Scheduled-Open','Scheduled-Closed','Escalated-Open','Escalated-Closed','In-Progress','Completed','Cancelled','Rescheduled']);
            $table->unsignedBigInteger('action_by')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('sub_department_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->integer('time_spent_minutes')->default(0);
            $table->date('support_date')->nullable();
            $table->time('support_time')->nullable();
            $table->string('support_location')->nullable();
            $table->string('support_venue')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->integer('time_spent_seconds')->default(0);
            $table->unsignedBigInteger('previous_history_id')->nullable();
            $table->text('action_description')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamp('sla_deadline')->nullable();
            $table->boolean('sla_breached')->default(false);
            $table->timestamps();
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
