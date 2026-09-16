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
    Route::apiResource('outages-api', OutageController::class);

    // Outage Ticket API Endpoints
    Route::apiResource('outage-tickets', OutageTicketController::class);

    // Additional API endpoints can be added here
    Route::post('outages/{outage}/update-status', [OutageController::class, 'updateStatus']);
    Route::post('outage-tickets/{ticket}/update-progress', [OutageTicketController::class, 'updateProgress']);
});

// Removed: duplicate GET outages/team-types/{teamType}/sub-teams and
// outages/sub-team-types/{subTeamType}/users routes previously registered
// here under auth:api. They resolved to the exact same URI as the
// session-authenticated versions in Modules\Outages\routes\web.php (both
// end up prefixed "api/outages/..."), and this file's auth:api-guarded copy
// was winning the match — so every browser session hit it and got 401,
// permanently breaking the Assigned Sub Team Type / Assigned To cascading
// dropdowns on the outage create/edit forms for every web user.
