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
     * Two bugs fixed here:
     * 1. The signature previously declared `handle(Request $request, Closure
     *    $next): Response`, which is incompatible with the parent
     *    Authenticate::handle($request, Closure $next, ...$guards) under
     *    PHP 8.1+'s stricter LSP checks — this was a fatal
     *    "Declaration must be compatible" error the moment this class was
     *    loaded (e.g. `php artisan route:list`), and would break bootstrapping
     *    routes that reference this middleware in some deploy configurations.
     * 2. The old body checked `$this->auth->guest()` with no guard specified,
     *    which resolves the *default* guard — 'web' (session-based, see
     *    config/auth.php `defaults.guard`) — never the 'mobile' Sanctum guard
     *    that config/auth.php actually defines for this. A stateless mobile
     *    request with a valid Bearer token has no web session, so `guest()`
     *    on the web guard is always true: every mobile API call protected by
     *    this middleware would 401 even with a perfectly valid token.
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? ['mobile'] : $guards;

        if (\Illuminate\Support\Facades\Auth::guard($guards[0])->guest()) {
            return response()->json([
                'message' => 'Unauthenticated. Please provide a valid token.'
            ], 401);
        }

        \Illuminate\Support\Facades\Auth::shouldUse($guards[0]);

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
