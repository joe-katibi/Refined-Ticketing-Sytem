<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class MobileAuthController extends Controller
{
    /**
     * Mobile login for field technicians and sales team
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if user has mobile access roles
        $mobileRoles = ['Field-Technician', 'Sales-Agent', 'outages-field-technician'];
        $hasAccess = $user->hasAnyRole($mobileRoles) || $user->hasRole('super-admin');

        if (!$hasAccess) {
            return response()->json([
                'message' => 'Access denied. This account does not have mobile app permissions.'
            ], 403);
        }

        // Create token for mobile device
        $token = $user->createToken($request->device_name)->plainTextToken;

        // Get user roles and permissions
        $roles = $user->getRoleNames();
        $permissions = $user->getAllPermissions()->pluck('name');

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $roles,
                'permissions' => $permissions,
                'is_field_technician' => $user->hasAnyRole(['Field-Technician', 'outages-field-technician']),
                'is_sales_team' => $user->hasRole('Sales-Agent'),
                'team_type_id' => $user->team_type_id,
                'sub_team_type_id' => $user->sub_team_type_id,
            ],
            'token' => $token,
            'message' => 'Login successful'
        ]);
    }

    /**
     * Mobile logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get current user profile
     */
    public function profile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
                'is_field_technician' => $user->hasAnyRole(['Field-Technician', 'outages-field-technician']),
                'is_sales_team' => $user->hasRole('Sales-Agent'),
                'team_type_id' => $user->team_type_id,
                'sub_team_type_id' => $user->sub_team_type_id,
                'created_at' => $user->created_at,
            ]
        ]);
    }

    /**
     * Update user location (for field technicians)
     */
    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric',
            'timestamp' => 'nullable|date',
        ]);

        $user = $request->user();

        // Store location in user_locations table
        DB::table('user_locations')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'accuracy' => $request->accuracy,
                'recorded_at' => $request->timestamp ?? now(),
                'updated_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'Location updated successfully'
        ]);
    }

    /**
     * Get user's location history
     */
    public function locationHistory(Request $request)
    {
        $user = $request->user();

        $locations = DB::table('user_locations')
            ->where('user_id', $user->id)
            ->orderBy('recorded_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'locations' => $locations
        ]);
    }
}
