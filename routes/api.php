<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Outages\Http\Controllers\OutageApiController;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileAppointmentController;
use App\Http\Controllers\Api\MobileOutageController;
use App\Http\Controllers\Api\MobileEscalationController;
use App\Http\Controllers\Api\MobileTestController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
  return $request->user();
});

// CORS Test Routes (Public)
Route::middleware(['mobile.cors'])->group(function () {
    Route::get('test/cors', [MobileTestController::class, 'test']);
    Route::get('test/health', [MobileTestController::class, 'health']);
    Route::options('test/{any}', function () {
        return response('', 200);
    })->where('any', '.*');
});

// CORS Test Routes (Authenticated)
Route::middleware(['mobile.cors', 'auth:sanctum'])->group(function () {
    Route::get('test/auth', [MobileTestController::class, 'authTest']);
});

// Mobile App Authentication Routes
Route::prefix('mobile')->middleware(['mobile.cors'])->group(function () {
    Route::post('login', [MobileAuthController::class, 'login']); // With CORS middleware

    Route::middleware(['mobile.auth', 'api'])->group(function () {
        Route::post('logout', [MobileAuthController::class, 'logout']);
        Route::get('profile', [MobileAuthController::class, 'profile']);
        Route::post('location', [MobileAuthController::class, 'updateLocation']);
        Route::get('location/history', [MobileAuthController::class, 'locationHistory']);

        // Debug endpoints
        Route::get('debug', [App\Http\Controllers\Api\MobileDebugController::class, 'debug']);
        Route::get('test', [App\Http\Controllers\Api\MobileDebugController::class, 'test']);

        // Appointments
        Route::get('appointments', [MobileAppointmentController::class, 'index']);
        Route::get('appointments/{id}', [MobileAppointmentController::class, 'show']);
        Route::put('appointments/{id}', [MobileAppointmentController::class, 'update']);
        Route::get('appointments/{id}/history', [MobileAppointmentController::class, 'history']);
        Route::post('appointments/{id}/photos', [MobileAppointmentController::class, 'uploadPhoto']);
        Route::get('appointments/{id}/photos', [MobileAppointmentController::class, 'photos']);
        Route::get('appointments/performance', [MobileAppointmentController::class, 'performance']);

        // Outages
        Route::get('outages', [MobileOutageController::class, 'index']);
        Route::get('outages/{id}', [MobileOutageController::class, 'show']);
        Route::put('outages/{id}', [MobileOutageController::class, 'update']);
        Route::get('outages/{id}/history', [MobileOutageController::class, 'history']);
        Route::post('outages/{id}/photos', [MobileOutageController::class, 'uploadPhoto']);
        Route::get('outages/{id}/photos', [MobileOutageController::class, 'photos']);
        Route::get('outages/performance', [MobileOutageController::class, 'performance']);

        // Mobile Escalations API (Sales Team)
        Route::prefix('escalations')->group(function () {
            Route::get('/', [MobileEscalationController::class, 'index']);
            Route::post('/', [MobileEscalationController::class, 'store']);
            Route::get('{id}', [MobileEscalationController::class, 'show']);
            Route::get('{id}/history', [MobileEscalationController::class, 'history']);
            Route::get('departments', [MobileEscalationController::class, 'departments']);
            Route::get('departments/{departmentId}/sub-departments', [MobileEscalationController::class, 'subDepartments']);
            Route::get('performance/metrics', [MobileEscalationController::class, 'performance']);
        });
    });
});

// Outage API routes for cascading dropdowns
Route::prefix('outages')
  ->middleware('auth')
  ->group(function () {
    Route::get('olts/{olt}/slots', [OutageApiController::class, 'getOltSlots']);
    Route::get('slots/{slot}/ports', [OutageApiController::class, 'getSlotPorts']);
    Route::get('team-types/{teamType}/sub-teams', [OutageApiController::class, 'getSubTeamTypes']);
    Route::get('sub-team-types/{subTeamType}/users', [OutageApiController::class, 'getSubTeamUsers']);
  });
