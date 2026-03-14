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
      Schema::create('escalations', function (Blueprint $table) {
        $table->id();
        $table->integer('escalation_id')->nullable();
        $table->string('ticket_id');
        $table->string('account_number')->nullable();
        $table->integer('category_id')->nullable();
        $table->integer('sub_category_id')->nullable();
        $table->integer('department_id')->nullable();
        $table->integer('sub_department_id')->nullable();
        $table->enum('escalation_type', ['no_appointment', 'appointment'])->nullable();
        $table->text('description')->nullable();
        $table->string('olt_id')->nullable();
        $table->string('slot_id')->nullable();
        $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
        $table->enum('status', ['Scheduled-Open','Scheduled-Closed','Escalated-Open','Escalated-Closed','In-Progress','Rescheduled'])->default('Scheduled-Open');
        $table->integer('created_by')->nullable();
        $table->integer('assigned_to')->nullable();
        $table->integer('edited_by')->nullable();
        $table->timestamp('sla_deadline')->nullable();
        $table->timestamp('closed_at')->nullable();
        $table->boolean('sla_breached')->default(false);
        $table->boolean('sla_breach_notified')->default(false);
        $table->unsignedBigInteger('appointment_type_id')->nullable();
        $table->date('support_date')->nullable();
        $table->time('support_time')->nullable();
        $table->string('support_location')->nullable();
        $table->string('support_venue')->nullable();
        $table->unsignedBigInteger('appointment_id')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('escalations');
    }
};
