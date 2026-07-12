<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    if (! Schema::hasTable('appointments')) {
      return;
    }

    $hasUniqueIndex = collect(DB::select('SHOW INDEX FROM appointments'))->contains(function ($index) {
      return $index->Column_name === 'account_number' && (int) $index->Non_unique === 0;
    });

    if (! $hasUniqueIndex) {
      return;
    }

    Schema::table('appointments', function (Blueprint $table) {
      // Drop the unique constraint on account_number
      $table->dropUnique(['account_number']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    if (! Schema::hasTable('appointments')) {
      return;
    }

    $hasUniqueIndex = collect(DB::select('SHOW INDEX FROM appointments'))->contains(function ($index) {
      return $index->Column_name === 'account_number' && (int) $index->Non_unique === 0;
    });

    if ($hasUniqueIndex) {
      return;
    }

    Schema::table('appointments', function (Blueprint $table) {
      // Re-add the unique constraint if needed to rollback
      $table->unique('account_number');
    });
  }
};
