<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Concurrency-safe, cross-database (MySQL/PostgreSQL) ticket numbering.
 *
 * Replaces the "read MAX(id)/last ticket, then +1" pattern used previously in
 * AppointmentController@store and Outage::generateTicketNumber(), which reads and
 * writes outside of any transaction/lock and lets two concurrent requests compute
 * the same "next" number. This service uses a dedicated `sequence_counters` table
 * (id, scope, next_number) and takes a row lock (`SELECT ... FOR UPDATE`, supported
 * identically on MySQL and PostgreSQL via Laravel's lockForUpdate()) inside a
 * transaction, so the increment is atomic per scope.
 */
class SequenceNumberService
{
    /**
     * Atomically returns the next number for the given scope (e.g. "appointment:SUP",
     * "outage:OUT") and persists the increment. Must be safe to call from inside an
     * existing transaction (uses a nested/savepoint transaction).
     */
    public static function next(string $scope): int
    {
        return DB::transaction(function () use ($scope) {
            $counter = DB::table('sequence_counters')->where('scope', $scope)->lockForUpdate()->first();

            if ($counter) {
                DB::table('sequence_counters')->where('id', $counter->id)->update([
                    'next_number' => $counter->next_number + 1,
                    'updated_at' => now(),
                ]);

                return $counter->next_number;
            }

            // No row yet for this scope. Attempt the insert inside a NESTED
            // DB::transaction() — Laravel issues a SAVEPOINT for a nested
            // transaction (on both MySQL and PostgreSQL), so if a concurrent
            // request wins the race on the unique `scope` index, only that
            // savepoint rolls back; the outer transaction (and connection) stays
            // usable. A plain try/catch around the insert would not be safe on
            // PostgreSQL, where any failed statement poisons the whole
            // transaction until an explicit rollback.
            try {
                DB::transaction(function () use ($scope) {
                    DB::table('sequence_counters')->insert([
                        'scope' => $scope,
                        'next_number' => 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                });

                return 1;
            } catch (\Illuminate\Database\QueryException $e) {
                $counter = DB::table('sequence_counters')->where('scope', $scope)->lockForUpdate()->first();

                if (!$counter) {
                    throw $e;
                }

                DB::table('sequence_counters')->where('id', $counter->id)->update([
                    'next_number' => $counter->next_number + 1,
                    'updated_at' => now(),
                ]);

                return $counter->next_number;
            }
        });
    }
}
