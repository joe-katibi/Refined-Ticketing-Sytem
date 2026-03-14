<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class QueryOptimizationService
{
    /**
     * Cache expensive dashboard queries
     */
    public function getCachedDashboardStats(string $module, int $cacheMinutes = 30): array
    {
        $cacheKey = "dashboard_stats_{$module}";
        
        return Cache::remember($cacheKey, $cacheMinutes * 60, function () use ($module) {
            return $this->generateDashboardStats($module);
        });
    }

    /**
     * Generate dashboard statistics
     */
    private function generateDashboardStats(string $module): array
    {
        switch (strtolower($module)) {
            case 'users':
                return $this->getUserStats();
            case 'outages':
                return $this->getOutageStats();
            case 'appointments':
                return $this->getAppointmentStats();
            case 'escalations':
                return $this->getEscalationStats();
            default:
                return [];
        }
    }

    /**
     * Optimized user statistics
     */
    private function getUserStats(): array
    {
        return [
            'total_users' => DB::table('users')->count(),
            'active_users' => DB::table('users')->where('user_status', 'Active')->count(),
            'inactive_users' => DB::table('users')->where('user_status', 'Inactive')->count(),
            'users_per_department' => DB::table('users')
                ->join('departments', 'users.department_id', '=', 'departments.id')
                ->select('departments.department_name', DB::raw('count(*) as user_count'))
                ->groupBy('departments.id', 'departments.department_name')
                ->orderBy('user_count', 'desc')
                ->get(),
            'recent_users' => DB::table('users')
                ->where('created_at', '>=', now()->subDays(30))
                ->count()
        ];
    }

    /**
     * Optimized outage statistics
     */
    private function getOutageStats(): array
    {
        if (!DB::getSchemaBuilder()->hasTable('outages')) {
            return [];
        }

        return [
            'total_outages' => DB::table('outages')->count(),
            'active_outages' => DB::table('outages')->whereIn('status', ['Open', 'In Progress'])->count(),
            'resolved_outages' => DB::table('outages')->where('status', 'Resolved')->count(),
            'sla_breached' => DB::table('outages')->where('sla_breached', true)->count(),
            'outages_by_priority' => DB::table('outages')
                ->select('priority', DB::raw('count(*) as count'))
                ->groupBy('priority')
                ->get(),
            'recent_outages' => DB::table('outages')
                ->where('created_at', '>=', now()->subDays(7))
                ->count()
        ];
    }

    /**
     * Optimized appointment statistics
     */
    private function getAppointmentStats(): array
    {
        if (!DB::getSchemaBuilder()->hasTable('appointments')) {
            return [];
        }

        return [
            'total_appointments' => DB::table('appointments')->count(),
            'pending_appointments' => DB::table('appointments')->where('status', 'Pending')->count(),
            'completed_appointments' => DB::table('appointments')->where('status', 'Completed')->count(),
            'today_appointments' => DB::table('appointments')
                ->whereDate('appointment_date', today())
                ->count(),
            'upcoming_appointments' => DB::table('appointments')
                ->where('appointment_date', '>', today())
                ->where('status', 'Scheduled')
                ->count()
        ];
    }

    /**
     * Optimized escalation statistics
     */
    private function getEscalationStats(): array
    {
        if (!DB::getSchemaBuilder()->hasTable('escalations')) {
            return [];
        }

        return [
            'total_escalations' => DB::table('escalations')->count(),
            'open_escalations' => DB::table('escalations')->where('status', 'Open')->count(),
            'resolved_escalations' => DB::table('escalations')->where('status', 'Resolved')->count(),
            'high_priority' => DB::table('escalations')->where('priority', 'High')->count(),
            'recent_escalations' => DB::table('escalations')
                ->where('created_at', '>=', now()->subDays(7))
                ->count()
        ];
    }

    /**
     * Clear cached statistics
     */
    public function clearCachedStats(string $module = null): void
    {
        if ($module) {
            Cache::forget("dashboard_stats_{$module}");
        } else {
            $modules = ['users', 'outages', 'appointments', 'escalations'];
            foreach ($modules as $mod) {
                Cache::forget("dashboard_stats_{$mod}");
            }
        }
    }

    /**
     * Optimize query with proper indexing hints
     */
    public function optimizeQuery(string $table, array $conditions = [], array $orderBy = []): \Illuminate\Database\Query\Builder
    {
        $query = DB::table($table);

        // Add conditions with proper index usage
        foreach ($conditions as $column => $value) {
            if (is_array($value)) {
                $query->whereIn($column, $value);
            } else {
                $query->where($column, $value);
            }
        }

        // Add ordering
        foreach ($orderBy as $column => $direction) {
            $query->orderBy($column, $direction);
        }

        return $query;
    }

    /**
     * Get table size and index information
     */
    public function getTableStats(): array
    {
        $tables = DB::select("SHOW TABLES");
        $stats = [];

        foreach ($tables as $table) {
            $tableName = array_values((array) $table)[0];
            
            $tableStats = DB::select("
                SELECT 
                    table_name,
                    table_rows,
                    data_length,
                    index_length,
                    (data_length + index_length) as total_size
                FROM information_schema.tables 
                WHERE table_schema = DATABASE() 
                AND table_name = ?
            ", [$tableName]);

            if (!empty($tableStats)) {
                $stats[$tableName] = [
                    'rows' => $tableStats[0]->table_rows,
                    'data_size' => $this->formatBytes($tableStats[0]->data_length),
                    'index_size' => $this->formatBytes($tableStats[0]->index_length),
                    'total_size' => $this->formatBytes($tableStats[0]->total_size)
                ];
            }
        }

        return $stats;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
