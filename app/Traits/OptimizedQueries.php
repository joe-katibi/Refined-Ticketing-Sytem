<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

trait OptimizedQueries
{
    /**
     * Scope for optimized user queries with eager loading
     */
    public function scopeWithOptimizedRelations(Builder $query, array $relations = [])
    {
        $defaultRelations = $this->getDefaultEagerLoadRelations();
        $relations = array_merge($defaultRelations, $relations);
        
        return $query->with($relations);
    }

    /**
     * Scope for active records only
     */
    public function scopeActive(Builder $query)
    {
        $statusColumn = $this->getStatusColumn();
        if ($statusColumn) {
            return $query->where($statusColumn, $this->getActiveStatus());
        }
        return $query;
    }

    /**
     * Scope for recent records
     */
    public function scopeRecent(Builder $query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for date range filtering
     */
    public function scopeDateRange(Builder $query, $startDate = null, $endDate = null, string $column = 'created_at')
    {
        if ($startDate) {
            $query->where($column, '>=', $startDate);
        }
        if ($endDate) {
            $query->where($column, '<=', $endDate);
        }
        return $query;
    }

    /**
     * Optimized search scope
     */
    public function scopeSearch(Builder $query, string $search, array $columns = [])
    {
        if (empty($search)) {
            return $query;
        }

        $searchColumns = !empty($columns) ? $columns : $this->getSearchableColumns();
        
        return $query->where(function ($q) use ($search, $searchColumns) {
            foreach ($searchColumns as $column) {
                $q->orWhere($column, 'like', "%{$search}%");
            }
        });
    }

    /**
     * Cached count for expensive queries
     */
    public function getCachedCount(string $cacheKey = null, int $minutes = 60): int
    {
        $cacheKey = $cacheKey ?: $this->getTable() . '_count';
        
        return Cache::remember($cacheKey, $minutes * 60, function () {
            return $this->count();
        });
    }

    /**
     * Get default eager load relations for the model
     */
    protected function getDefaultEagerLoadRelations(): array
    {
        return property_exists($this, 'defaultEagerLoad') ? $this->defaultEagerLoad : [];
    }

    /**
     * Get the status column name for the model
     */
    protected function getStatusColumn(): ?string
    {
        $possibleColumns = ['status', 'user_status', 'department_status', 'service_status'];
        
        foreach ($possibleColumns as $column) {
            if (in_array($column, $this->getFillable())) {
                return $column;
            }
        }
        
        return null;
    }

    /**
     * Get the active status value
     */
    protected function getActiveStatus(): string
    {
        return property_exists($this, 'activeStatusValue') ? $this->activeStatusValue : 'Active';
    }

    /**
     * Get searchable columns for the model
     */
    protected function getSearchableColumns(): array
    {
        if (property_exists($this, 'searchableColumns')) {
            return $this->searchableColumns;
        }

        // Default searchable columns
        $defaultColumns = ['name', 'title', 'description', 'email'];
        $fillable = $this->getFillable();
        
        return array_intersect($defaultColumns, $fillable);
    }

    /**
     * Optimized pagination with cursor-based pagination for large datasets
     */
    public function scopeOptimizedPaginate(Builder $query, int $perPage = 15, string $cursorColumn = 'id')
    {
        return $query->orderBy($cursorColumn, 'desc')->cursorPaginate($perPage);
    }

    /**
     * Chunk processing for large datasets
     */
    public function processInChunks(callable $callback, int $chunkSize = 1000)
    {
        return $this->chunk($chunkSize, $callback);
    }
}
