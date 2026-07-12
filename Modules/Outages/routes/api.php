<?php

use Illuminate\Support\Facades\Route;
use Modules\Outages\Http\Controllers\OutageController;
use Modules\Outages\Http\Controllers\OutageTicketController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->group(function () {
    // Outage API Endpoints
    Route::apiResource('outages', OutageController::class);

    // Outage Ticket API Endpoints
    Route::apiResource('outage-tickets', OutageTicketController::class);

    // Additional API endpoints can be added here
    Route::post('outages/{outage}/update-status', [OutageController::class, 'updateStatus']);
    Route::post('outage-tickets/{ticket}/update-progress', [OutageTicketController::class, 'updateProgress']);

    // Dynamic team assignment endpoints
    Route::get('outages/team-types/{teamType}/sub-teams', [OutageController::class, 'getSubTeamTypes']);
    Route::get('outages/sub-team-types/{subTeamType}/users', [OutageController::class, 'getUsersBySubTeamType']);
});
