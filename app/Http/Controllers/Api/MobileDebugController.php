<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MobileDebugController extends Controller
{
    /**
     * Debug endpoint to help troubleshoot mobile app issues
     */
    public function debug(Request $request)
    {
        try {
            $user = $request->user();
            
            // Log the request details
            Log::info('MobileDebugController::debug - Request received', [
                'user_id' => $user ? $user->id : 'not authenticated',
                'user_email' => $user ? $user->email : 'not authenticated',
                'user_team_type_id' => $user ? $user->team_type_id : 'not authenticated',
                'request_headers' => $request->headers->all(),
                'request_url' => $request->fullUrl(),
                'request_method' => $request->method(),
                'request_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Debug endpoint working',
                'user_authenticated' => $user ? true : false,
                'user_data' => $user ? [
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name,
                    'team_type_id' => $user->team_type_id,
                ] : null,
                'timestamp' => now()->toISOString(),
                'server_time' => now()->format('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            Log::error('MobileDebugController::debug - Error occurred', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'timestamp' => now()->toISOString(),
            ], 500);
        }
    }

    /**
     * Test endpoint with minimal data
     */
    public function test(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Test endpoint working',
            'data' => [
                'string_field' => 'test_string',
                'number_field' => 123,
                'boolean_field' => true,
                'null_field_as_string' => '',
                'null_field_as_number' => 0,
                'array_field' => [
                    'nested_string' => 'nested_value',
                    'nested_number' => 456,
                    'nested_null_string' => '',
                ]
            ],
            'timestamp' => now()->toISOString(),
        ]);
    }
}
