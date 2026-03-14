<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MobileTestController extends Controller
{
    /**
     * Test endpoint for Flutter connectivity
     */
    public function test(Request $request)
    {
        Log::info('MobileTestController::test - CORS test endpoint called', [
            'origin' => $request->header('Origin'),
            'user_agent' => $request->header('User-Agent'),
            'method' => $request->method(),
            'headers' => $request->headers->all()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'CORS is working correctly!',
            'timestamp' => now()->toISOString(),
            'origin' => $request->header('Origin'),
            'method' => $request->method(),
            'cors_enabled' => true,
            'api_version' => '1.0.0',
            'server_info' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'server_time' => now()->toDateTimeString(),
                'timezone' => config('app.timezone'),
            ]
        ]);
    }

    /**
     * Test authenticated endpoint
     */
    public function authTest(Request $request)
    {
        $user = $request->user();
        
        Log::info('MobileTestController::authTest - Authenticated test endpoint called', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'origin' => $request->header('Origin')
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Authentication and CORS working correctly!',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'team_type_id' => $user->team_type_id,
            ],
            'timestamp' => now()->toISOString(),
            'authenticated' => true,
        ]);
    }

    /**
     * Health check endpoint
     */
    public function health(Request $request)
    {
        return response()->json([
            'status' => 'healthy',
            'api' => 'mobile-ticketing-api',
            'version' => '1.0.0',
            'timestamp' => now()->toISOString(),
            'cors_headers' => [
                'Access-Control-Allow-Origin' => $request->header('Origin', 'http://localhost:3000'),
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
                'Access-Control-Allow-Credentials' => 'true',
            ]
        ]);
    }
}
