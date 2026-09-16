<?php

use Illuminate\Support\Facades\Route;
use Modules\Outages\Http\Controllers\AffectedAreaController;
use Modules\Outages\Http\Controllers\AffectedServiceController;
use Modules\Outages\Http\Controllers\CustomerController;
use Modules\Outages\Http\Controllers\FatController;
use Modules\Outages\Http\Controllers\FdtController;
use Modules\Outages\Http\Controllers\OltController;
use Modules\Outages\Http\Controllers\OltUploadController;
use Modules\Outages\Http\Controllers\OutageController;
use Modules\Outages\Http\Controllers\OutageDashboardController;
use Modules\Outages\Http\Controllers\OutageFinalReasonController;
use Modules\Outages\Http\Controllers\OutageReportController;
use Modules\Outages\Http\Controllers\OutageTicketController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['auth', 'verified'])->group(function () {
    // Outage Management
    Route::resource('outages', OutageController::class)->middleware([
        'index' => 'permission:view-outage-menu',
        'create' => 'permission:view-create-outage',
        'store' => 'permission:view-create-outage',
        'show' => 'permission:view-view-outage',
        'edit' => 'permission:view-edit-outage',
        'update' => 'permission:view-edit-outage',
        'destroy' => 'permission:view-delete-outage',
    ]);

    // Additional outage routes
    Route::patch('outages/{outage}/status', [OutageController::class, 'updateStatus'])->name('outages.updateStatus')->middleware('permission:view-edit-outage');
    Route::post('outages/{outage}/progress', [OutageController::class, 'storeProgress'])->name('outages.progress.store')->middleware('permission:view-edit-outage');
    Route::get('outages/{outage}/activity', [OutageController::class, 'activity'])->name('outages.activity')->middleware('permission:view-history-outage');

    // Assigned Outages and My Outages
    Route::get('assigned-outages', [OutageController::class, 'assignedOutages'])->name('outages.assigned')->middleware('permission:view-assigned-outage-menu');
    Route::get('my-outages', [OutageController::class, 'myOutages'])->name('outages.my-outages')->middleware('permission:view-my-outage-menu');
    // RolesSeeder grants Field-Technician 'view-assigned-my-outage-edit'
    // for this exact page (distinct from the admin 'view-edit-outage') —
    // gating on only 'view-edit-outage' 403'd every technician who clicked
    // Edit on their own My Outages list. See the identical fix for
    // Modules\Appointment\routes\web.php's edit_assigned/update_assigned.
    Route::get('my-outages/{outage}/edit', [OutageController::class, 'editMyOutage'])->name('outages.my-outages.edit')->middleware('permission:view-edit-outage|view-assigned-my-outage-edit');
    Route::put('my-outages/{outage}', [OutageController::class, 'updateMyOutage'])->name('outages.my-outages.update')->middleware('permission:view-edit-outage|view-assigned-my-outage-edit');

    // Outage Tickets
    Route::resource('outage-tickets', OutageTicketController::class)->middleware('permission:view-outage-menu');

    // Final Reasons Management
    Route::resource('final-reasons', OutageFinalReasonController::class, [
        'as' => 'outages',
    ])->middleware('permission:view-outage-final-reasons-menu');

    // Affected Areas Management
    Route::resource('affected-areas', AffectedAreaController::class, [
        'as' => 'outages',
    ])->middleware('permission:view-affected-areas-menu');

    // Affected Services Management
    Route::resource('affected-services', AffectedServiceController::class, [
        'as' => 'outages',
    ])->middleware('permission:view-affected-services-menu');

    // Reports
    Route::prefix('outage-reports')->group(function () {
        Route::get('/', [OutageReportController::class, 'index'])->name('outage-reports.index')->middleware('permission:view-outage-download-reports');

        // Individual Report Pages
        Route::get('/sla', [OutageReportController::class, 'sla'])->name('outage-reports.sla')->middleware('permission:view-outage-download-reports');
        Route::get('/productivity', [OutageReportController::class, 'productivity'])->name('outage-reports.productivity')->middleware('permission:view-outage-download-reports');
        Route::get('/sla-breakdown', [OutageReportController::class, 'slaBreakdown'])->name('outage-reports.sla-breakdown')->middleware('permission:view-outage-download-reports');
        Route::get('/trends', [OutageReportController::class, 'trends'])->name('outage-reports.trends')->middleware('permission:view-outage-download-reports');
        Route::get('/impact', [OutageReportController::class, 'impact'])->name('outage-reports.impact')->middleware('permission:view-outage-download-reports');
        Route::get('/root-cause', [OutageReportController::class, 'rootCause'])->name('outage-reports.root-cause')->middleware('permission:view-outage-download-reports');

        // Individual Report Exports
        Route::get('/sla/export', [OutageReportController::class, 'slaExport'])->name('outage-reports.sla.export');
        Route::get('/productivity/export', [OutageReportController::class, 'productivityExport'])->name('outage-reports.productivity.export');
        Route::get('/sla-breakdown/export', [OutageReportController::class, 'slaBreakdownExport'])->name('outage-reports.sla-breakdown.export');
        Route::get('/trends/export', [OutageReportController::class, 'trendsExport'])->name('outage-reports.trends.export');
        Route::get('/impact/export', [OutageReportController::class, 'impactExport'])->name('outage-reports.impact.export');
        Route::get('/root-cause/export', [OutageReportController::class, 'rootCauseExport'])->name('outage-reports.root-cause.export');

        // Combined Export
        Route::get('/export', [OutageReportController::class, 'export'])->name('outage-reports.export');
    });

    // Dashboard
    Route::get('/outage-dashboard', [OutageDashboardController::class, 'index'])->name('outage-dashboard.index')->middleware('permission:view-dashboard-outage');

    // OLT Management Routes
    Route::resource('olts', OltController::class)->middleware('permission:view-olt-management-menu');
    Route::get('olts/{olt}/slots/create', [OltController::class, 'createSlot'])->name('olts.slots.create')->middleware('permission:view-olt-management-menu');
    Route::post('olts/{olt}/slots', [OltController::class, 'storeSlot'])->name('olts.slots.store')->middleware('permission:view-olt-management-menu');
    Route::get('slots/{slot}/ports/create', [OltController::class, 'createPort'])->name('slots.ports.create')->middleware('permission:view-olt-management-menu');
    Route::post('slots/{slot}/ports', [OltController::class, 'storePort'])->name('slots.ports.store')->middleware('permission:view-olt-management-menu');
    Route::get('slots/{slot}/edit', [OltController::class, 'editSlot'])->name('slots.edit')->middleware('permission:view-olt-management-menu');
    Route::put('slots/{slot}', [OltController::class, 'updateSlot'])->name('slots.update')->middleware('permission:view-olt-management-menu');
    Route::get('ports/{port}/edit', [OltController::class, 'editPort'])->name('ports.edit')->middleware('permission:view-olt-management-menu');
    Route::put('ports/{port}', [OltController::class, 'updatePort'])->name('ports.update')->middleware('permission:view-olt-management-menu');

    // AJAX endpoints for OLT management
    Route::get('api/olts/{olt}/slots', [OltController::class, 'getSlots'])->name('api.olts.slots');
    Route::get('api/slots/{slot}/ports', [OltController::class, 'getPorts'])->name('api.slots.ports');

    // FDT Management Routes
    Route::resource('fdts', FdtController::class)->middleware('permission:view-olt-management-menu');
    Route::get('ports/{ponPort}/fdts/create', [FdtController::class, 'create'])->name('ports.fdts.create')->middleware('permission:view-olt-management-menu');
    Route::post('ports/{ponPort}/fdts', [FdtController::class, 'store'])->name('ports.fdts.store')->middleware('permission:view-olt-management-menu');

    // FAT Management Routes
    Route::resource('fats', FatController::class)->middleware('permission:view-olt-management-menu');
    Route::get('fdts/{fdt}/fats/create', [FatController::class, 'create'])->name('fdts.fats.create')->middleware('permission:view-olt-management-menu');
    Route::post('fdts/{fdt}/fats', [FatController::class, 'store'])->name('fdts.fats.store')->middleware('permission:view-olt-management-menu');

    // AJAX endpoints for FDT and FAT management
    Route::get('api/ports/{ponPort}/fdts', [FdtController::class, 'getFdts'])->name('api.ports.fdts');
    Route::get('api/fdts/{fdt}/fats', [FatController::class, 'getFats'])->name('api.fdts.fats');

    // Customer Management Routes
    // Registered before the resource route below so the literal path
    // "customers/search" isn't shadowed by the resource's "customers/{customer}"
    // show route, which would otherwise try (and fail) to bind "search" as
    // a customer ID.
    Route::get('customers/search', [CustomerController::class, 'search'])->name('customers.search');
    Route::resource('customers', CustomerController::class)->middleware('permission:view-olt-management-menu');
    Route::get('fats/{fat}/customers/create', [CustomerController::class, 'create'])->name('fats.customers.create')->middleware('permission:view-olt-management-menu');
    Route::post('fats/{fat}/customers', [CustomerController::class, 'store'])->name('fats.customers.store')->middleware('permission:view-olt-management-menu');

    // OLT / FDT / FAT / Customer bulk Excel upload
    Route::get('olt-upload', [OltUploadController::class, 'create'])->name('olt-upload.create')->middleware('permission:view-olt-management-create');
    Route::post('olt-upload', [OltUploadController::class, 'store'])->name('olt-upload.store')->middleware('permission:view-olt-management-create');
    Route::get('olt-upload/template', [OltUploadController::class, 'template'])->name('olt-upload.template')->middleware('permission:view-olt-management-create');

    // AJAX endpoints for outage creation cascading dropdowns
    Route::get('api/outages/olts/{olt}/slots', [OutageController::class, 'getOltSlots'])->name('api.outages.olt.slots');
    Route::get('api/outages/slots/{slot}/ports', [OutageController::class, 'getSlotPorts'])->name('api.outages.slot.ports');

    // AJAX endpoints for team assignment cascading dropdowns
    Route::get('api/outages/team-types/{teamType}/sub-teams', [OutageController::class, 'getSubTeamsByType'])->name('api.outages.teamtype.subteams');
    Route::get('api/outages/sub-team-types/{subTeamType}/users', [OutageController::class, 'getUsersBySubTeamType'])->name('api.outages.subteamtype.users');

    // Outage Notifications - Temporarily commented out until controller issues are resolved
    /*
    Route::prefix('outages/notifications')->group(function () {
        Route::get('/', [OutageNotificationController::class, 'index'])->name('outages.notifications.index')->middleware('permission:view-outage-notification-menu');
        Route::post('/{notification}/mark-read', [OutageNotificationController::class, 'markAsRead'])->name('outages.notifications.mark-read')->middleware('permission:view-outage-notification-mark-read');
        Route::post('/mark-all-read', [OutageNotificationController::class, 'markAllAsRead'])->name('outages.notifications.mark-all-read')->middleware('permission:view-outage-notification-mark-read');
        Route::get('/unread-count', [OutageNotificationController::class, 'getUnreadCount'])->name('outages.notifications.unread-count');
        Route::get('/recent', [OutageNotificationController::class, 'getRecent'])->name('outages.notifications.recent');
    });
    */
});
