<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Query Optimization Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for database query optimization
    | including caching, indexing, and performance monitoring settings.
    |
    */

    'cache' => [
        /*
        |--------------------------------------------------------------------------
        | Dashboard Cache Settings
        |--------------------------------------------------------------------------
        |
        | Configure caching duration for dashboard statistics and expensive queries
        |
        */
        'dashboard_stats_ttl' => env('DASHBOARD_CACHE_TTL', 30), // minutes
        'user_stats_ttl' => env('USER_STATS_CACHE_TTL', 60), // minutes
        'report_cache_ttl' => env('REPORT_CACHE_TTL', 120), // minutes
        
        /*
        |--------------------------------------------------------------------------
        | Query Result Caching
        |--------------------------------------------------------------------------
        |
        | Enable/disable caching for specific query types
        |
        */
        'enable_dashboard_cache' => env('ENABLE_DASHBOARD_CACHE', true),
        'enable_report_cache' => env('ENABLE_REPORT_CACHE', true),
        'enable_lookup_cache' => env('ENABLE_LOOKUP_CACHE', true),
    ],

    'pagination' => [
        /*
        |--------------------------------------------------------------------------
        | Pagination Settings
        |--------------------------------------------------------------------------
        |
        | Configure pagination limits and cursor pagination thresholds
        |
        */
        'default_per_page' => env('DEFAULT_PER_PAGE', 15),
        'max_per_page' => env('MAX_PER_PAGE', 100),
        'cursor_pagination_threshold' => env('CURSOR_PAGINATION_THRESHOLD', 10000), // records
    ],

    'performance' => [
        /*
        |--------------------------------------------------------------------------
        | Performance Monitoring
        |--------------------------------------------------------------------------
        |
        | Configure slow query logging and performance monitoring
        |
        */
        'slow_query_threshold' => env('SLOW_QUERY_THRESHOLD', 100), // milliseconds
        'enable_query_logging' => env('ENABLE_QUERY_LOGGING', false),
        'log_slow_queries' => env('LOG_SLOW_QUERIES', true),
        
        /*
        |--------------------------------------------------------------------------
        | Bulk Operations
        |--------------------------------------------------------------------------
        |
        | Configure chunk sizes for bulk operations
        |
        */
        'bulk_chunk_size' => env('BULK_CHUNK_SIZE', 100),
        'export_chunk_size' => env('EXPORT_CHUNK_SIZE', 1000),
    ],

    'indexes' => [
        /*
        |--------------------------------------------------------------------------
        | Index Optimization
        |--------------------------------------------------------------------------
        |
        | Configure which indexes should be created for optimal performance
        |
        */
        'auto_create_indexes' => env('AUTO_CREATE_INDEXES', true),
        'analyze_query_patterns' => env('ANALYZE_QUERY_PATTERNS', false),
    ],

    'eager_loading' => [
        /*
        |--------------------------------------------------------------------------
        | Default Eager Loading
        |--------------------------------------------------------------------------
        |
        | Configure default relationships to eager load for each model
        |
        */
        'users' => ['roles', 'department', 'teamType'],
        'outages' => ['assignedTeam', 'assignee', 'reporter', 'olt'],
        'appointments' => ['assignedTeam', 'assignee', 'createdBy'],
        'escalations' => ['assignedTeam', 'assignee', 'createdBy', 'category'],
    ],
];
