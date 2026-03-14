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
    Schema::table('outages', function (Blueprint $table) {
      $table
        ->foreignId('sub_team_type_id')
        ->nullable()
        ->after('assigned_team_id')
        ->constrained('sub_team_types')
        ->onDelete('set null');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('outages', function (Blueprint $table) {
      $table->dropForeign(['sub_team_type_id']);
      $table->dropColumn('sub_team_type_id');
    });
  }
};
