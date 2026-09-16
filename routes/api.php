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

// CORS preflight catch-all for the mobile API. None of the mobile/* routes
// below register an OPTIONS method, and MobileCorsMiddleware is applied as
// route middleware — so a browser's preflight OPTIONS request never matches
// any route, Laravel throws MethodNotAllowedHttpException before route
// middleware runs, and the resulting 405 carries no CORS headers at all.
// Native apps (Android/iOS) never hit this because they don't send CORS
// preflight requests, but any browser-based client (a web build, or testing
// the API from a browser) is unconditionally blocked. This explicit OPTIONS
// route runs mobile.cors directly so preflight gets a real 200 + CORS headers.
Route::options('mobile/{any}', fn () => response('', 200))->where('any', '.*')->middleware('mobile.cors');

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
        // Static-segment routes (performance) must be registered BEFORE the
        // {id} wildcard — otherwise Laravel matches "performance" as {id}
        // first and it never reaches the intended handler. Same fix applied
        // below to outages and escalations, which had the identical bug.
        Route::get('appointments', [MobileAppointmentController::class, 'index']);
        // Flutter's api_service.dart calls /performance/metrics (matching the
        // escalations endpoint's own convention); the backend only ever
        // registered /performance (no /metrics), a second mismatch beyond the
        // route-ordering bug above. Both paths now resolve.
        Route::get('appointments/performance/metrics', [MobileAppointmentController::class, 'performance']);
        Route::get('appointments/performance', [MobileAppointmentController::class, 'performance']);
        Route::get('appointments/{id}', [MobileAppointmentController::class, 'show']);
        Route::put('appointments/{id}', [MobileAppointmentController::class, 'update']);
        Route::get('appointments/{id}/history', [MobileAppointmentController::class, 'history']);
        Route::post('appointments/{id}/photos', [MobileAppointmentController::class, 'uploadPhoto']);
        Route::get('appointments/{id}/photos', [MobileAppointmentController::class, 'photos']);

        // Outages
        Route::get('outages', [MobileOutageController::class, 'index']);
        Route::get('outages/performance/metrics', [MobileOutageController::class, 'performance']);
        Route::get('outages/performance', [MobileOutageController::class, 'performance']);
        Route::get('outages/{id}', [MobileOutageController::class, 'show']);
        Route::put('outages/{id}', [MobileOutageController::class, 'update']);
        Route::get('outages/{id}/history', [MobileOutageController::class, 'history']);
        Route::post('outages/{id}/photos', [MobileOutageController::class, 'uploadPhoto']);
        Route::get('outages/{id}/photos', [MobileOutageController::class, 'photos']);

        // Mobile Escalations API (Sales Team)
        Route::prefix('escalations')->group(function () {
            Route::get('/', [MobileEscalationController::class, 'index']);
            Route::post('/', [MobileEscalationController::class, 'store']);
            Route::get('departments', [MobileEscalationController::class, 'departments']);
            Route::get('departments/{departmentId}/sub-departments', [MobileEscalationController::class, 'subDepartments']);
            Route::get('categories', [MobileEscalationController::class, 'categories']);
            Route::get('categories/{categoryId}/sub-categories', [MobileEscalationController::class, 'subCategories']);
            Route::get('performance/metrics', [MobileEscalationController::class, 'performance']);
            Route::get('{id}', [MobileEscalationController::class, 'show']);
            Route::get('{id}/history', [MobileEscalationController::class, 'history']);
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
