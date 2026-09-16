<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Concurrency-safe ticket numbering, per spec section 4.1:
 * "Do not generate numbers by SELECT MAX(id)+1. Use a dedicated sequence/counter
 * table with a database transaction and row locking so two simultaneous requests
 * cannot receive the same number."
 *
 * AppointmentController@store previously derived the next number from
 * `ORDER BY id DESC LIMIT 1` with no lock and no transaction — two concurrent
 * requests can read the same "last ticket" and both compute the same next number,
 * producing duplicate ticket IDs (the store() method even logged a
 * "$duplicateCount" without ever acting on it).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sequence_counters', function (Blueprint $table) {
            $table->id();
            $table->string('scope', 100)->comment('e.g. appointment:SUP, outage:OUT');
            $table->unsignedBigInteger('next_number')->default(1);
            $table->timestamps();

            $table->unique('scope');
        });

        // Backfill from any tickets that already exist, so switching a
        // populated (e.g. production) database over to this table does not
        // reissue ticket numbers that are already in use.
        $this->backfillFrom('appointment', 'appointments', 'appointment_ticket_id');
        $this->backfillFrom('outage', 'outages', 'ticket_number');
    }

    private function backfillFrom(string $module, string $table, string $column): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        $maxByPrefix = [];
        \Illuminate\Support\Facades\DB::table($table)->select($column)->whereNotNull($column)
            ->orderBy($column)
            ->chunk(500, function ($rows) use ($column, &$maxByPrefix) {
                foreach ($rows as $row) {
                    if (preg_match('/^([A-Za-z]+)-(\d+)$/', (string) $row->{$column}, $m)) {
                        $prefix = strtoupper($m[1]);
                        $number = (int) $m[2];
                        $maxByPrefix[$prefix] = max($maxByPrefix[$prefix] ?? 0, $number);
                    }
                }
            });

        foreach ($maxByPrefix as $prefix => $max) {
            \Illuminate\Support\Facades\DB::table('sequence_counters')->updateOrInsert(
                ['scope' => $module . ':' . $prefix],
                ['next_number' => $max + 1, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sequence_counters');
    }
};
