<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFirstTimeLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated and it's their first login
        if (auth()->check() && auth()->user()->is_first_login) {
            // Skip redirect if already on password change page
            if (!$request->routeIs('password.first-time') && !$request->routeIs('password.update-first-time')) {
                return redirect()->route('password.first-time');
            }
        }

        return $next($request);
    }
}
