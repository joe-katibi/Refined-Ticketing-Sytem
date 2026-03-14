<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OptimizeQueries
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Enable query logging in development
        if (config('app.debug')) {
            DB::enableQueryLog();
        }

        $response = $next($request);

        // Log slow queries in development
        if (config('app.debug')) {
            $queries = DB::getQueryLog();
            
            foreach ($queries as $query) {
                // Log queries that take longer than 100ms
                if ($query['time'] > 100) {
                    Log::warning('Slow Query Detected', [
                        'sql' => $query['query'],
                        'bindings' => $query['bindings'],
                        'time' => $query['time'] . 'ms',
                        'url' => $request->fullUrl()
                    ]);
                }
            }
        }

        return $response;
    }
}
