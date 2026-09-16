<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The original sequence_counters migration backfilled from
 * appointments/outages but missed escalations (ListController::store() was
 * only switched over to SequenceNumberService afterwards, in a later commit)
 * — so the counter started at 1 despite existing ESC-N tickets already using
 * that number, producing a real duplicate ticket_id on the very next create.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('escalations') || !Schema::hasTable('sequence_counters')) {
            return;
        }

        // Parsed in PHP rather than a raw SUBSTRING/CAST expression so this
        // works identically on MySQL and PostgreSQL.
        $max = 0;
        DB::table('escalations')->select('ticket_id')->where('ticket_id', 'like', 'ESC-%')
            ->orderBy('ticket_id')
            ->chunk(500, function ($rows) use (&$max) {
                foreach ($rows as $row) {
                    if (preg_match('/^ESC-(\d+)$/', (string) $row->ticket_id, $m)) {
                        $max = max($max, (int) $m[1]);
                    }
                }
            });

        if (!$max) {
            return;
        }

        DB::table('sequence_counters')->updateOrInsert(
            ['scope' => 'escalation:ESC'],
            ['next_number' => $max + 1, 'updated_at' => now(), 'created_at' => now()]
        );
    }

    public function down(): void
    {
        // No-op: this only corrects counter state.
    }
};
