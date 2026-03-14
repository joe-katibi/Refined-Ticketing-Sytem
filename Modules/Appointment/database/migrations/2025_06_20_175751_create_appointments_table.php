<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('appointments', function (Blueprint $table) {
      $table->id();
      $table->string('account_number')->nullable();
      $table->string('appointment_ticket_id')->unique();
      $table->string('escalation_ticket_id')->nullable();
      $table->string('outage_ticket_id')->nullable();
      $table->string('appointment_id')->nullable();
      $table->string('appointment_type_id')->nullable();
      $table->unsignedBigInteger('sub_department_id')->nullable();
      $table->unsignedBigInteger('category_id')->nullable();
      $table->unsignedBigInteger('sub_category_id')->nullable();
      $table->text('description_notes')->nullable();
      $table->string('priority')->nullable();
      $table->enum('escalation_type', ['no_appointment', 'appointment'])->nullable();
      $table->string('status')->nullable();
      $table->date('scheduled_date')->nullable();
      $table->time('scheduled_time')->nullable();
      $table->date('completed_date')->nullable();
      $table->time('completed_time')->nullable();
      $table->unsignedBigInteger('assigned_team_id')->nullable();
      $table->unsignedBigInteger('escalated_team_id')->nullable();
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
      $table->string('olt_id', 50)->nullable();
      $table->string('slot_id', 50)->nullable();
      $table->unsignedBigInteger('final_reason_id')->nullable();
      $table->decimal('optical_level', 8, 2)->nullable();
      $table->boolean('custom_confirmation')->default(false);
      $table->text('notes')->nullable();
      $table->text('comment')->nullable();
      $table->date('rescheduled_date')->nullable();
      $table->time('rescheduled_time')->nullable();
      $table->unsignedBigInteger('created_by')->nullable();
      $table->unsignedBigInteger('edited_by')->nullable();
      $table->timestamp('closed_at')->nullable();
      $table->timestamp('escalated_at')->nullable();
      $table->timestamps();
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('appointments');
  }
};
