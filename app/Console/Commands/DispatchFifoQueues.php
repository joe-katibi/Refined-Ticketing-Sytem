<?php

namespace App\Console\Commands;

use App\Services\Fifo\FifoQueueService;
use Illuminate\Console\Command;

/**
 * Scheduled FIFO auto-dispatch (spec section 19: DispatchFifoQueuesJob).
 * Drains each module's queue by repeatedly assigning the oldest eligible
 * ticket until either the queue is empty or the front of the queue has no
 * eligible agent (at which point it stops rather than looping forever).
 */
class DispatchFifoQueues extends Command
{
    protected $signature = 'fifo:dispatch {module? : escalation|appointment|outage — omit to dispatch all}';

    protected $description = 'Auto-assign oldest eligible waiting tickets to available agents in each FIFO queue';

    public function handle(FifoQueueService $fifo): int
    {
        $modules = $this->argument('module') ? [$this->argument('module')] : ['escalation', 'appointment', 'outage'];

        foreach ($modules as $module) {
            $assigned = 0;

            while (true) {
                $result = $fifo->assignNext($module);

                if (!$result || !$result['agent']) {
                    break;
                }

                $assigned++;
            }

            $this->info("[{$module}] assigned {$assigned} ticket(s).");
        }

        return self::SUCCESS;
    }
}
