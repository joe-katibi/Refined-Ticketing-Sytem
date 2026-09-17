<?php

namespace App\Traits;

/**
 * Buckets an elapsed-hours value into fixed bands for "time since raised"
 * style report columns. Shared between ReportsController and
 * AppointmentReportExport so the on-screen report and its Excel export
 * always agree on which band a ticket falls into.
 */
trait BucketsTicketAge
{
    /** @var int[] Upper bound (hours) of each band, in ascending order. */
    protected array $ageBucketBoundaries = [3, 6, 9, 12, 15, 18, 21, 24, 48, 72];

    protected function ageBucketLabel(float $hours): string
    {
        $lower = 0;

        foreach ($this->ageBucketBoundaries as $boundary) {
            if ($hours <= $boundary) {
                return "{$lower}–{$boundary} hrs";
            }
            $lower = $boundary;
        }

        return '72+ hrs';
    }
}
