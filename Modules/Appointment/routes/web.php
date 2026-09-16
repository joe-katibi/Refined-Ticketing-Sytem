<?php

use Illuminate\Support\Facades\Route;
use Modules\Appointment\Http\Controllers\AppointmentController;
use Modules\Appointment\Http\Controllers\AppointmentFinalReasonController;
use Modules\Appointment\Http\Controllers\AppointmentHistoryController;
use Modules\Appointment\Http\Controllers\AppointmentStatusController;
use Modules\Appointment\Http\Controllers\AppointmentTypeController;
use Modules\Appointment\Http\Controllers\DashboardController;
use Modules\Appointment\Http\Controllers\InstallationUploadController;
use Modules\Appointment\Http\Controllers\NotificationController;
use Modules\Appointment\Http\Controllers\ReportsController;
use Modules\Appointment\Http\Controllers\SiteVisitFinalReasonController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard Routes
    Route::prefix('appointment-dashboard')
        ->name('appointment.dashboard.')
        ->group(function () {
            Route::get('/', [DashboardController::class, 'index'])
                ->name('index')
                ->middleware('permission:view-dashboard-appointment');
        });

    // Notification Routes
    Route::prefix('appointment-notifications')
        ->name('appointment.notifications.')
        ->group(function () {
            Route::get('/', [NotificationController::class, 'index'])
                ->name('index')
                ->middleware('permission:view-notifications-appointment');
            Route::post('/mark-read/{notification}', [NotificationController::class, 'markAsRead'])
                ->name('mark-read')
                ->middleware('permission:view-notifications-appointment');
            Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])
                ->name('mark-all-read')
                ->middleware('permission:view-notifications-appointment');
        });

    // Appointments Resource Routes
    Route::prefix('appointments')
        ->name('appointment.appointments.')
        ->group(function () {
            Route::get('/appointment', [AppointmentController::class, 'index'])->name('index');
            Route::get('/list', [AppointmentController::class, 'list'])->name('list');
            Route::get('/assigned', [AppointmentController::class, 'assigned'])
                ->name('assigned')
                ->middleware('permission:view-assigned-appointments-menu');
            Route::get('/my-appointments', [AppointmentController::class, 'myAppointments'])
                ->name('my_appointments')
                ->middleware('permission:view-my-appointments-menu');
            // RolesSeeder grants dispatchers/admins 'view-appointment-edit' and
            // grants Field-Technician a distinct 'view-my-appointment-edit' for
            // this exact page — gating on only the first permission meant a
            // technician saw the "Edit" link on their own My Appointments page
            // but got a 403 the moment they clicked it, with no way to progress
            // or close a ticket assigned to them.
            Route::get('/assigned/{appointment}/edit', [AppointmentController::class, 'editAssigned'])
                ->name('edit_assigned')
                ->middleware('permission:view-appointment-edit|view-my-appointment-edit');
            Route::put('/assigned/{appointment}', [AppointmentController::class, 'updateAssigned'])
                ->name('update_assigned')
                ->middleware('permission:view-appointment-edit|view-my-appointment-edit');
            Route::get('/create', [AppointmentController::class, 'create'])
                ->name('create')
                ->middleware('permission:view-appointment-create');
            Route::post('/', [AppointmentController::class, 'store'])
                ->name('store')
                ->middleware('permission:view-appointment-create');

            // AJAX Routes for getting sub-types
            Route::get('/get-sub-types/{type_id}', [AppointmentController::class, 'getSubTypes'])->name('get-sub-types');

            // AJAX route for the "Assigned To" cascade on the edit form
            Route::get('/sub-team-types/{subTeamType}/users', [AppointmentController::class, 'getUsersBySubTeamType'])->name('sub-team-types.users');

            // AJAX Routes for getting slots by OLT
            Route::get('/get-slots/{olt_id}', [AppointmentController::class, 'getSlots'])->name('get-slots');

            // Appointment Histories
            Route::get('/histories', [AppointmentHistoryController::class, 'index'])->name('histories.index');

            // Single appointment history
            Route::get('{appointment}/histories', [AppointmentHistoryController::class, 'show'])->name('histories.show');

            // Single appointment routes
            Route::prefix('{appointment}')->whereNumber('appointment')->group(function () {
                Route::get('/', [AppointmentController::class, 'show'])
                    ->name('show')
                    ->middleware('permission:view-appointment-view');
                Route::get('/edit', [AppointmentController::class, 'edit'])
                    ->name('edit')
                    ->middleware('permission:view-appointment-edit');
                Route::put('/', [AppointmentController::class, 'update'])
                    ->name('update')
                    ->middleware('permission:view-appointment-edit');
                Route::delete('/', [AppointmentController::class, 'destroy'])
                    ->name('destroy')
                    ->middleware('permission:view-appointment-delete');
            });
        });

    // Installation Data Bulk Upload
    Route::prefix('installation-upload')
        ->name('installation-upload.')
        ->group(function () {
            Route::get('/', [InstallationUploadController::class, 'create'])
                ->name('create')
                ->middleware('permission:view-appointment-create');
            Route::post('/', [InstallationUploadController::class, 'store'])
                ->name('store')
                ->middleware('permission:view-appointment-create');
            Route::get('/template', [InstallationUploadController::class, 'template'])
                ->name('template')
                ->middleware('permission:view-appointment-create');
        });

    // Final Reasons
    Route::prefix('appointment-final-reasons')
        ->name('appointment.final-reasons.')
        ->group(function () {
            Route::get('/', [AppointmentFinalReasonController::class, 'index'])
                ->name('index')
                ->middleware('permission:view-final-reasons-appointment');
            Route::get('/create', [AppointmentFinalReasonController::class, 'create'])
                ->name('create')
                ->middleware('permission:view-final-reasons-appointment');
            Route::post('/', [AppointmentFinalReasonController::class, 'store'])
                ->name('store')
                ->middleware('permission:view-final-reasons-appointment');

            // Single final reason routes
            Route::prefix('{final_reason}')->group(function () {
                Route::get('/edit', [AppointmentFinalReasonController::class, 'edit'])
                    ->name('edit')
                    ->middleware('permission:view-final-reasons-appointment');
                Route::put('/', [AppointmentFinalReasonController::class, 'update'])
                    ->name('update')
                    ->middleware('permission:view-final-reasons-appointment');
                Route::delete('/', [AppointmentFinalReasonController::class, 'destroy'])
                    ->name('destroy')
                    ->middleware('permission:view-final-reasons-appointment');
            });
        });

    // Appointment Types
    Route::prefix('appointment-types')
        ->name('appointment.types.')
        ->group(function () {
            // Main types routes
            Route::get('/', [AppointmentTypeController::class, 'index'])
                ->name('index')
                ->middleware('permission:view-types-appointment');
            Route::get('/create', [AppointmentTypeController::class, 'create'])
                ->name('create')
                ->middleware('permission:view-types-appointment');
            Route::post('/', [AppointmentTypeController::class, 'store'])
                ->name('store')
                ->middleware('permission:view-types-appointment');

            // Single type routes
            Route::prefix('{appointment_type}')->group(function () {
                Route::get('/', [AppointmentTypeController::class, 'show'])
                    ->name('show')
                    ->middleware('permission:view-types-appointment');
                Route::get('/edit', [AppointmentTypeController::class, 'edit'])
                    ->name('edit')
                    ->middleware('permission:view-types-appointment');
                Route::put('/', [AppointmentTypeController::class, 'update'])
                    ->name('update')
                    ->middleware('permission:view-types-appointment');
                Route::delete('/', [AppointmentTypeController::class, 'destroy'])
                    ->name('destroy')
                    ->middleware('permission:view-types-appointment');

                // Sub-types routes
                Route::prefix('sub-types')
                    ->name('sub-types.')
                    ->group(function () {
                        Route::get('/create', [AppointmentTypeController::class, 'createSubType'])
                            ->name('create')
                            ->middleware('permission:view-types-appointment');
                        Route::post('/', [AppointmentTypeController::class, 'storeSubType'])
                            ->name('store')
                            ->middleware('permission:view-types-appointment');

                        // Single sub-type routes
                        Route::prefix('{sub_type}')->group(function () {
                            Route::get('/edit', [AppointmentTypeController::class, 'editSubType'])
                                ->name('edit')
                                ->middleware('permission:view-types-appointment');
                            Route::put('/', [AppointmentTypeController::class, 'updateSubType'])
                                ->name('update')
                                ->middleware('permission:view-types-appointment');
                            Route::delete('/', [AppointmentTypeController::class, 'destroySubType'])
                                ->name('destroy')
                                ->middleware('permission:view-types-appointment');
                        });
                    });
            });
        });

    // Appointment Statuses
    Route::prefix('appointment-statuses')
        ->name('appointment.statuses.')
        ->group(function () {
            Route::get('/', [AppointmentStatusController::class, 'index'])
                ->name('index')
                ->middleware('permission:view-statuses-appointment');
            Route::get('/create', [AppointmentStatusController::class, 'create'])
                ->name('create')
                ->middleware('permission:create-statuses-appointment');
            Route::post('/', [AppointmentStatusController::class, 'store'])
                ->name('store')
                ->middleware('permission:create-statuses-appointment');

            // Toggle status active/inactive
            Route::patch('/{status}/toggle', [AppointmentStatusController::class, 'toggleStatus'])
                ->name('toggle')
                ->middleware('permission:edit-statuses-appointment');

            // Single status routes
            Route::prefix('{status}')->group(function () {
                Route::get('/', [AppointmentStatusController::class, 'show'])
                    ->name('show')
                    ->middleware('permission:view-statuses-appointment');
                Route::get('/edit', [AppointmentStatusController::class, 'edit'])
                    ->name('edit')
                    ->middleware('permission:edit-statuses-appointment');
                Route::put('/', [AppointmentStatusController::class, 'update'])
                    ->name('update')
                    ->middleware('permission:edit-statuses-appointment');
                Route::delete('/', [AppointmentStatusController::class, 'destroy'])
                    ->name('destroy')
                    ->middleware('permission:delete-statuses-appointment');
            });
        });

    // Reports Routes
    Route::prefix('appointment-reports')
        ->name('appointment.reports.')
        ->group(function () {
            Route::get('/', [ReportsController::class, 'index'])
                ->name('index')
                ->middleware('permission:view-reports-appointment');
            Route::get('/sla', [ReportsController::class, 'slaReport'])
                ->name('sla')
                ->middleware('permission:view-reports-appointment');
            Route::get('/team-productivity', [ReportsController::class, 'teamProductivityReport'])
                ->name('team-productivity')
                ->middleware('permission:view-reports-appointment');
            Route::get('/sub-team-productivity', [ReportsController::class, 'subTeamProductivityReport'])
                ->name('sub-team-productivity')
                ->middleware('permission:view-reports-appointment');
            Route::get('/assigned-team-productivity', [ReportsController::class, 'assignedTeamProductivityReport'])
                ->name('assigned-team-productivity')
                ->middleware('permission:view-reports-appointment');
            Route::get('/final-reason', [ReportsController::class, 'finalReasonReport'])
                ->name('final-reason')
                ->middleware('permission:view-reports-appointment');
            Route::get('/export-excel', [ReportsController::class, 'exportExcel'])
                ->name('export-excel')
                ->middleware('permission:view-reports-appointment');
        });

    // Site Visit Routes
    Route::prefix('site-visit')
        ->name('site-visit.')
        ->group(function () {
            // Infrastructure Routes
            Route::prefix('infrastructure')
                ->name('infrastructure.')
                ->group(function () {
                    Route::get('/history', [AppointmentHistoryController::class, 'infrastructureHistory'])
                        ->name('history')
                        ->middleware('permission:view-site-visit-appointment');
                    Route::get('/new', [AppointmentController::class, 'infrastructureNew'])
                        ->name('new')
                        ->middleware('permission:view-site-visit-appointment');
                });

            // NOC Routes
            Route::prefix('noc')
                ->name('noc.')
                ->group(function () {
                    Route::get('/history', [AppointmentHistoryController::class, 'nocHistory'])
                        ->name('history')
                        ->middleware('permission:view-site-visit-appointment');
                    Route::get('/new', [AppointmentController::class, 'nocNew'])
                        ->name('new')
                        ->middleware('permission:view-site-visit-appointment');
                });

            // Design Routes
            Route::prefix('design')
                ->name('design.')
                ->group(function () {
                    Route::get('/history', [AppointmentHistoryController::class, 'designHistory'])
                        ->name('history')
                        ->middleware('permission:view-site-visit-appointment');
                    Route::get('/new', [AppointmentController::class, 'designNew'])
                        ->name('new')
                        ->middleware('permission:view-site-visit-appointment');
                });

            // Site Visit Final Reasons Routes
            Route::prefix('final-reasons')
                ->name('final-reasons.')
                ->group(function () {
                    Route::get('/', [SiteVisitFinalReasonController::class, 'index'])
                        ->name('index')
                        ->middleware('permission:view-site-visit-appointment');
                    Route::get('/create', [SiteVisitFinalReasonController::class, 'create'])
                        ->name('create')
                        ->middleware('permission:view-site-visit-appointment');
                    Route::post('/', [SiteVisitFinalReasonController::class, 'store'])
                        ->name('store')
                        ->middleware('permission:view-site-visit-appointment');

                    // Single final reason routes
                    Route::prefix('{final_reason}')->group(function () {
                        Route::get('/', [SiteVisitFinalReasonController::class, 'show'])
                            ->name('show')
                            ->middleware('permission:view-site-visit-appointment');
                        Route::get('/edit', [SiteVisitFinalReasonController::class, 'edit'])
                            ->name('edit')
                            ->middleware('permission:view-site-visit-appointment');
                        Route::put('/', [SiteVisitFinalReasonController::class, 'update'])
                            ->name('update')
                            ->middleware('permission:view-site-visit-appointment');
                        Route::delete('/', [SiteVisitFinalReasonController::class, 'destroy'])
                            ->name('destroy')
                            ->middleware('permission:view-site-visit-appointment');
                    });
                });
        });
});
