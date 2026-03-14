<?php

namespace App\Http\Controllers;

use App\Services\QueryOptimizationService;
use App\Traits\OptimizedQueries;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Cache;

class OptimizedController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests, OptimizedQueries;

    protected $queryOptimizationService;

    public function __construct(QueryOptimizationService $queryOptimizationService = null)
    {
        $this->queryOptimizationService = $queryOptimizationService ?? app(QueryOptimizationService::class);
    }

    /**
     * Optimized pagination helper
     */
    protected function getPaginatedResults($query, Request $request, int $defaultPerPage = 15)
    {
        $perPage = min($request->get('per_page', $defaultPerPage), 100); // Limit max per page
        
        // Use cursor pagination for better performance on large datasets
        if ($request->has('cursor')) {
            return $query->cursorPaginate($perPage);
        }
        
        return $query->paginate($perPage);
    }

    /**
     * Apply common filters to query
     */
    protected function applyFilters($query, Request $request, array $filterableColumns = [])
    {
        // Search filter
        if ($request->filled('search')) {
            $searchColumns = $filterableColumns['search'] ?? ['name', 'title', 'description'];
            $query->search($request->search, $searchColumns);
        }

        // Status filter
        if ($request->filled('status') && in_array('status', $filterableColumns)) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('start_date') || $request->filled('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        // Department filter
        if ($request->filled('department_id') && in_array('department_id', $filterableColumns)) {
            $query->where('department_id', $request->department_id);
        }

        // Team filter
        if ($request->filled('team_id') && in_array('team_id', $filterableColumns)) {
            $query->where('team_id', $request->team_id);
        }

        return $query;
    }

    /**
     * Cache expensive queries
     */
    protected function getCachedData(string $cacheKey, callable $callback, int $minutes = 60)
    {
        return Cache::remember($cacheKey, $minutes * 60, $callback);
    }

    /**
     * Get optimized dashboard data
     */
    protected function getDashboardData(string $module, int $cacheMinutes = 30): array
    {
        return $this->queryOptimizationService->getCachedDashboardStats($module, $cacheMinutes);
    }

    /**
     * Clear cache for specific module
     */
    protected function clearModuleCache(string $module): void
    {
        $this->queryOptimizationService->clearCachedStats($module);
    }

    /**
     * Optimized JSON response for AJAX requests
     */
    protected function jsonResponse($data, string $message = 'Success', int $status = 200)
    {
        return response()->json([
            'success' => $status >= 200 && $status < 300,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    /**
     * Handle bulk operations efficiently
     */
    protected function processBulkOperation(array $ids, callable $operation, int $chunkSize = 100)
    {
        $chunks = array_chunk($ids, $chunkSize);
        $results = [];

        foreach ($chunks as $chunk) {
            $results[] = $operation($chunk);
        }

        return $results;
    }
}
