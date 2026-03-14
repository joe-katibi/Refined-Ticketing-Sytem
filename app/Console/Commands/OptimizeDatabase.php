<?php

namespace App\Console\Commands;

use App\Services\QueryOptimizationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class OptimizeDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:optimize {--analyze : Analyze table statistics} {--clear-cache : Clear query cache} {--show-stats : Show table statistics}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize database performance by analyzing tables and clearing cache';

    protected $queryOptimizationService;

    public function __construct(QueryOptimizationService $queryOptimizationService)
    {
        parent::__construct();
        $this->queryOptimizationService = $queryOptimizationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting database optimization...');

        if ($this->option('clear-cache')) {
            $this->clearQueryCache();
        }

        if ($this->option('analyze')) {
            $this->analyzeTables();
        }

        if ($this->option('show-stats')) {
            $this->showTableStats();
        }

        if (!$this->option('clear-cache') && !$this->option('analyze') && !$this->option('show-stats')) {
            // Run all optimizations by default
            $this->clearQueryCache();
            $this->analyzeTables();
            $this->showTableStats();
        }

        $this->info('Database optimization completed!');
        return Command::SUCCESS;
    }

    /**
     * Clear query cache
     */
    private function clearQueryCache(): void
    {
        $this->info('Clearing query cache...');
        
        Cache::flush();
        $this->queryOptimizationService->clearCachedStats();
        
        $this->line('✓ Query cache cleared');
    }

    /**
     * Analyze database tables
     */
    private function analyzeTables(): void
    {
        $this->info('Analyzing database tables...');

        $tables = $this->getTables();
        
        foreach ($tables as $table) {
            try {
                DB::statement("ANALYZE TABLE `{$table}`");
                $this->line("✓ Analyzed table: {$table}");
            } catch (\Exception $e) {
                $this->error("✗ Failed to analyze table {$table}: " . $e->getMessage());
            }
        }
    }

    /**
     * Show table statistics
     */
    private function showTableStats(): void
    {
        $this->info('Database Table Statistics:');
        $this->line('');

        $stats = $this->queryOptimizationService->getTableStats();

        $headers = ['Table', 'Rows', 'Data Size', 'Index Size', 'Total Size'];
        $rows = [];

        foreach ($stats as $tableName => $tableStats) {
            $rows[] = [
                $tableName,
                number_format($tableStats['rows']),
                $tableStats['data_size'],
                $tableStats['index_size'],
                $tableStats['total_size']
            ];
        }

        $this->table($headers, $rows);
    }

    /**
     * Get all table names
     */
    private function getTables(): array
    {
        $tables = DB::select('SHOW TABLES');
        $tableNames = [];

        foreach ($tables as $table) {
            $tableNames[] = array_values((array) $table)[0];
        }

        return $tableNames;
    }
}
