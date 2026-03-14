# Database Query Optimization Implementation Guide

This guide provides a comprehensive database optimization system to prevent performance issues as your project scales.

## Overview

The optimization system includes:
- **Database Indexes** for faster query execution
- **Query Caching** for expensive operations
- **Eager Loading** to prevent N+1 queries
- **Optimized Pagination** for large datasets
- **Performance Monitoring** for slow query detection

## Implementation Components

### 1. Database Indexes Migration

**File**: `database/migrations/2025_08_17_083500_optimize_database_performance_indexes.php`

Creates comprehensive indexes for:
- **Users table**: status, department, team type, email, username
- **Outages table**: status, priority, assigned team, dates
- **Appointments table**: status, priority, dates
- **Escalations table**: status, priority, assigned team
- **System tables**: roles, permissions, departments

**Composite indexes** for common filter combinations:
- `status + priority`
- `assigned_team + status`
- `created_at + status`

### 2. OptimizedQueries Trait

**File**: `app/Traits/OptimizedQueries.php`

Provides reusable query optimization methods:
```php
// Usage in models
use App\Traits\OptimizedQueries;

class YourModel extends Model
{
    use OptimizedQueries;
    
    protected $defaultEagerLoad = ['relation1', 'relation2'];
    protected $searchableColumns = ['name', 'email'];
}

// Query examples
$users = User::withOptimizedRelations()
    ->active()
    ->search($request->search)
    ->dateRange($startDate, $endDate)
    ->paginate(15);
```

### 3. Query Optimization Service

**File**: `app/Services/QueryOptimizationService.php`

Handles expensive dashboard queries with caching:
```php
// Usage in controllers
$stats = $this->queryOptimizationService->getCachedDashboardStats('users', 30);
```

### 4. Optimized Controller Base Class

**File**: `app/Http/Controllers/OptimizedController.php`

Provides optimized methods for common operations:
```php
class YourController extends OptimizedController
{
    public function index(Request $request)
    {
        $query = YourModel::withOptimizedRelations();
        $query = $this->applyFilters($query, $request, ['status', 'department_id']);
        $results = $this->getPaginatedResults($query, $request);
        
        return view('your.view', compact('results'));
    }
}
```

### 5. Performance Monitoring Middleware

**File**: `app/Http/Middleware/OptimizeQueries.php`

Logs slow queries in development:
- Tracks queries > 100ms
- Logs to Laravel log with query details
- Helps identify optimization opportunities

### 6. Database Optimization Command

**File**: `app/Console/Commands/OptimizeDatabase.php`

Maintenance command for database optimization:
```bash
# Clear cache and analyze tables
php artisan db:optimize

# Show table statistics
php artisan db:optimize --show-stats

# Clear cache only
php artisan db:optimize --clear-cache

# Analyze tables only  
php artisan db:optimize --analyze
```

## How to Apply to Existing Controllers

### Step 1: Update Models

Add the OptimizedQueries trait to your models:

```php
<?php

namespace App\Models;

use App\Traits\OptimizedQueries;
use Illuminate\Database\Eloquent\Model;

class YourModel extends Model
{
    use OptimizedQueries;
    
    // Define default relationships to eager load
    protected $defaultEagerLoad = ['department', 'assignedTeam', 'createdBy'];
    
    // Define searchable columns
    protected $searchableColumns = ['name', 'title', 'description'];
    
    // Define active status value
    protected $activeStatusValue = 'Active';
    
    // Optimize relationships with select statements
    public function department()
    {
        return $this->belongsTo(Department::class)->select(['id', 'department_name']);
    }
}
```

### Step 2: Update Controllers

Extend OptimizedController and use optimization methods:

```php
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\OptimizedController;
use Illuminate\Http\Request;

class YourController extends OptimizedController
{
    public function index(Request $request)
    {
        // Start with optimized query
        $query = YourModel::withOptimizedRelations();
        
        // Apply filters efficiently
        $query = $this->applyFilters($query, $request, [
            'search' => ['name', 'title'],
            'status',
            'department_id',
            'team_id'
        ]);
        
        // Get paginated results
        $results = $this->getPaginatedResults($query, $request);
        
        // Cache expensive data
        $dashboardData = $this->getDashboardData('your_module');
        
        return view('your.view', compact('results', 'dashboardData'));
    }
}
```

### Step 3: Update Views (Optional)

Add pagination info and search optimization:

```html
<!-- Efficient search form -->
<form method="GET" class="mb-3">
    <div class="row">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" 
                   placeholder="Search..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-control">
                <option value="">All Status</option>
                <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
                <option value="Inactive" {{ request('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </div>
</form>

<!-- Optimized pagination -->
{{ $results->appends(request()->query())->links() }}
```

## Configuration

### Cache Settings

**File**: `config/query-optimization.php`

```php
'cache' => [
    'dashboard_stats_ttl' => 30, // minutes
    'enable_dashboard_cache' => true,
],

'pagination' => [
    'default_per_page' => 15,
    'max_per_page' => 100,
],

'performance' => [
    'slow_query_threshold' => 100, // milliseconds
    'log_slow_queries' => true,
]
```

### Environment Variables

Add to `.env`:
```env
# Cache settings
DASHBOARD_CACHE_TTL=30
ENABLE_DASHBOARD_CACHE=true
ENABLE_REPORT_CACHE=true

# Performance settings
SLOW_QUERY_THRESHOLD=100
LOG_SLOW_QUERIES=true

# Pagination settings
DEFAULT_PER_PAGE=15
MAX_PER_PAGE=100
```

## Running the Optimization

### 1. Apply Database Indexes
```bash
php artisan migrate
```

### 2. Optimize Database
```bash
php artisan db:optimize
```

### 3. Monitor Performance
```bash
# Check table statistics
php artisan db:optimize --show-stats

# Clear cache when needed
php artisan db:optimize --clear-cache
```

## Benefits

### Performance Improvements
- **50-90% faster queries** with proper indexing
- **Reduced memory usage** with selective eager loading
- **Faster pagination** on large datasets
- **Cached dashboard queries** prevent repeated expensive operations

### Scalability
- **Cursor pagination** for datasets > 10,000 records
- **Chunk processing** for bulk operations
- **Optimized search** with indexed columns
- **Efficient filtering** with composite indexes

### Monitoring
- **Slow query detection** in development
- **Table statistics** for optimization insights
- **Cache hit rates** for performance tuning
- **Query pattern analysis** for further optimization

## Maintenance

### Scheduled Tasks
The system automatically:
- Cleans up report downloads every 8 hours
- Can be extended to optimize tables periodically

### Manual Maintenance
```bash
# Weekly optimization
php artisan db:optimize --analyze

# Clear cache after major updates
php artisan db:optimize --clear-cache

# Monitor table growth
php artisan db:optimize --show-stats
```

This optimization system will keep your application performant as data grows, without affecting the current UI or pagination functionality.
