<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MobileCorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Handle preflight OPTIONS requests
        if ($request->getMethod() === "OPTIONS") {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', $this->getAllowedOrigin($request))
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Accept, Authorization, Content-Type, X-Requested-With, X-CSRF-TOKEN, X-Socket-ID, Origin, Cache-Control, Pragma, Expires, Last-Modified, If-Modified-Since, If-Unmodified-Since, If-None-Match, If-Match, Range, Content-Range, X-HTTP-Method-Override')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Max-Age', '86400');
        }

        $response = $next($request);

        // Add CORS headers to the response
        $response->headers->set('Access-Control-Allow-Origin', $this->getAllowedOrigin($request));
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Accept, Authorization, Content-Type, X-Requested-With, X-CSRF-TOKEN, X-Socket-ID, Origin, Cache-Control, Pragma, Expires, Last-Modified, If-Modified-Since, If-Unmodified-Since, If-None-Match, If-Match, Range, Content-Range, X-HTTP-Method-Override');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Max-Age', '86400');

        // Expose additional headers for pagination and authentication
        $response->headers->set('Access-Control-Expose-Headers', 'Authorization, X-Pagination-Current-Page, X-Pagination-Page-Count, X-Pagination-Per-Page, X-Pagination-Total-Count');

        return $response;
    }

    /**
     * Get the allowed origin for the request
     */
    private function getAllowedOrigin(Request $request): string
    {
        $origin = $request->header('Origin');
        
        $allowedOrigins = [
            'http://localhost:3000',
            'http://127.0.0.1:3000',
            'http://localhost:8080',
            'http://127.0.0.1:8080',
            'http://localhost:5000',
            'http://127.0.0.1:5000',
            'http://localhost:1118',
            'http://127.0.0.1:1118',
            'http://localhost:7233',
            'http://127.0.0.1:7233',
            'http://localhost:8000',
            'http://127.0.0.1:8000',
            'capacitor://localhost',
            'ionic://localhost',
            'http://localhost',
            'https://localhost',
        ];

        // Check if the origin is in our allowed list
        if (in_array($origin, $allowedOrigins)) {
            return $origin;
        }

        // Check if origin matches localhost pattern
        if ($origin && (
            str_starts_with($origin, 'http://localhost:') ||
            str_starts_with($origin, 'http://127.0.0.1:') ||
            str_starts_with($origin, 'https://localhost:') ||
            str_starts_with($origin, 'https://127.0.0.1:')
        )) {
            return $origin;
        }

        // Default to localhost:3000 for Flutter development
        return 'http://localhost:3000';
    }
}
