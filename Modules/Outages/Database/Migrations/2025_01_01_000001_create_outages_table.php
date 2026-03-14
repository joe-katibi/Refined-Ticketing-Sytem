<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutagesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('outages', function (Blueprint $table) {
      $table->id();
      $table->string('ticket_number')->unique();
      $table->string('title');
      $table->text('description')->nullable();
      $table->enum('status', ['Reported', 'In Progress', 'Resolved', 'Closed'])->default('Reported');
      $table->enum('priority', ['Low', 'Medium', 'High', 'Critical'])->default('Medium');
      $table->dateTime('start_time');
      $table->dateTime('end_time')->nullable();
      $table->text('root_cause')->nullable();
      $table->text('resolution')->nullable();
      $table->json('impacted_areas')->nullable();
      $table->json('impacted_services')->nullable();

      // Team and user assignments
      $table
        ->foreignId('assigned_team_id')
        ->nullable()
        ->constrained('team_types');
      $table
        ->foreignId('assigned_to')
        ->nullable()
        ->constrained('users');
      $table->foreignId('reported_by')->constrained('users');
      $table
        ->foreignId('resolved_by')
        ->nullable()
        ->constrained('users');

      // SLA tracking
      $table->boolean('sla_breached')->default(false);
      $table->dateTime('sla_breach_time')->nullable();

      // Timestamps and soft deletes
      $table->timestamps();
      $table->softDeletes();

      // Additional metadata
      $table->foreignId('created_by')->constrained('users');
      $table
        ->foreignId('updated_by')
        ->nullable()
        ->constrained('users');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('outages');
  }
}
