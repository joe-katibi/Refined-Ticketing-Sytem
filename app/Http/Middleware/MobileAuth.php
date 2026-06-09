<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Symfony\Component\HttpFoundation\Response;

class MobileAuth extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // For mobile API, return JSON error instead of redirect
        if ($this->auth->guest()) {
            return response()->json([
                'message' => 'Unauthenticated. Please provide a valid token.'
            ], 401);
        }

        return $next($request);
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     * Override to return null for API requests
     */
    protected function redirectTo(Request $request): ?string
    {
        return null; // Never redirect for mobile API
    }
}
