<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentStatusHistoriesTableLegacy extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    if (Schema::hasTable('appointment_status_histories')) {
      return;
    }

    Schema::create('appointment_status_histories', function (Blueprint $table) {
      $table->id();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('appointment_status_histories');
  }
}
