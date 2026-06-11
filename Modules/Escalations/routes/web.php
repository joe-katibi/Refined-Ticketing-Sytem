<?php

use Illuminate\Support\Facades\Route;
use Modules\Escalations\app\Http\Controllers\EscalationController;
use Modules\Escalations\Http\Controllers\EscalationsController;
use Modules\Escalations\Http\Controllers\ListController;
use Modules\Escalations\Http\Controllers\CategoryController;
use Modules\Escalations\Http\Controllers\SourceController;
use Modules\Escalations\Http\Controllers\DashboardController;
use Modules\Escalations\Http\Controllers\NotificationController;
use Modules\Escalations\Http\Controllers\ReportsController;

Route::middleware(['web', 'auth'])->group(function () {
  // List routes
  Route::prefix('list')
    ->name('list.')
    ->group(function () {
      Route::get('/subcategories/{category_id}', [ListController::class, 'getSubcategories'])->name('subcategories');
      Route::get('/escalate', [ListController::class, 'index'])->name('index');
      Route::get('/create', [ListController::class, 'create'])
        ->name('create')
        ->middleware('permission:view-create-escalation');
      Route::post('/', [ListController::class, 'store'])
        ->name('store')
        ->middleware('permission:view-create-escalation');
      Route::get('/{id}', [ListController::class, 'show'])
        ->name('show')
        ->middleware('permission:view-view-escalation');
      Route::get('/{id}/edit', [ListController::class, 'edit'])
        ->name('edit')
        ->middleware('permission:view-edit-escalation');
      Route::put('/{id}', [ListController::class, 'update'])
        ->name('update')
        ->middleware('permission:view-edit-escalation');
      Route::delete('/{id}', [ListController::class, 'destroy'])
        ->name('destroy')
        ->middleware('permission:view-delete-escalation');
    });

  // Route to get subcategories for a category
  Route::get('/escalations/categories/{category}/subcategories', [
    EscalationsController::class,
    'getSubcategories',
  ])->name('escalations.subcategories');

  // Route to get slots for an OLT
  Route::get('/escalations/olts/{olt}/slots', [EscalationsController::class, 'getOltSlots'])->name(
    'escalations.olt-slots'
  );

  // Escalation routes with history
  Route::prefix('escalation')->group(function () {
    Route::get('/', [EscalationsController::class, 'index'])->name('escalations.index');
    Route::get('/create', [EscalationsController::class, 'create'])
      ->name('escalations.create')
      ->middleware('permission:view-create-escalation');
    Route::post('/', [EscalationsController::class, 'store'])
      ->name('escalations.store')
      ->middleware('permission:view-create-escalation');

    // Single escalation routes
    Route::prefix('{escalation}')->group(function () {
      Route::get('/', [EscalationsController::class, 'show'])
        ->name('escalations.show')
        ->middleware('permission:view-view-escalation');
      Route::get('/edit', [EscalationsController::class, 'edit'])
        ->name('escalations.edit')
        ->middleware('permission:view-edit-escalation');
      Route::put('/', [EscalationsController::class, 'update'])
        ->name('escalations.update')
        ->middleware('permission:view-edit-escalation');
      Route::patch('/deactivate', [EscalationsController::class, 'deactivate'])
        ->name('escalations.deactivate')
        ->middleware('permission:view-edit-escalation');
      Route::patch('/inactivate', [EscalationsController::class, 'inactivate'])
        ->name('escalations.inactivate')
        ->middleware('permission:view-edit-escalation');

      // History routes
      Route::get('/history', [EscalationController::class, 'history'])
        ->name('escalations.history')
        ->middleware('permission:view-history-escalation');
      Route::post('/status', [EscalationController::class, 'updateStatus'])
        ->name('escalations.update-status')
        ->middleware('permission:view-edit-escalation');
      Route::post('/assign', [EscalationController::class, 'assign'])
        ->name('escalations.assign')
        ->middleware('permission:view-edit-escalation');
      Route::post('/department', [EscalationController::class, 'changeDepartment'])
        ->name('escalations.change-department')
        ->middleware('permission:view-edit-escalation');
      Route::post('/notes', [EscalationController::class, 'addNote'])
        ->name('escalations.add-note')
        ->middleware('permission:view-edit-escalation');
    });
  });

  // Category routes
  Route::prefix('escalation-category')
    ->name('escalation-category.')
    ->group(function () {
      Route::get('/', [CategoryController::class, 'index'])
        ->name('index')
        ->middleware('permission:view-categories-escalation');
      Route::get('/create', [CategoryController::class, 'create'])
        ->name('create')
        ->middleware('permission:view-categories-escalation');
      Route::post('/', [CategoryController::class, 'store'])
        ->name('store')
        ->middleware('permission:view-categories-escalation');

      // Single category routes
      Route::prefix('{category}')->group(function () {
        Route::get('/', [CategoryController::class, 'show'])
          ->name('show')
          ->middleware('permission:view-categories-escalation');
        Route::get('/edit', [CategoryController::class, 'edit'])
          ->name('edit')
          ->middleware('permission:view-categories-escalation');
        Route::put('/', [CategoryController::class, 'update'])
          ->name('update')
          ->middleware('permission:view-categories-escalation');
        Route::patch('/inactive', [CategoryController::class, 'inactive'])
          ->name('inactive')
          ->middleware('permission:view-categories-escalation');
        Route::patch('/active', [CategoryController::class, 'active'])
          ->name('active')
          ->middleware('permission:view-categories-escalation');

        // Subcategory routes (nested)
        Route::prefix('subcategories')
          ->name('subcategories.')
          ->group(function () {
            Route::get('/', [CategoryController::class, 'subcategoryIndex'])
              ->name('index')
              ->middleware('permission:view-categories-escalation');
            Route::get('/create', [CategoryController::class, 'subcategoryCreate'])
              ->name('create')
              ->middleware('permission:view-categories-escalation');
            Route::post('/', [CategoryController::class, 'subcategoryStore'])
              ->name('store')
              ->middleware('permission:view-categories-escalation');

            // Single subcategory routes
            Route::prefix('{subcategory}')->group(function () {
              Route::get('/', [CategoryController::class, 'subcategoryShow'])
                ->name('show')
                ->middleware('permission:view-categories-escalation');
              Route::get('/edit', [CategoryController::class, 'subcategoryEdit'])
                ->name('edit')
                ->middleware('permission:view-categories-escalation');
              Route::put('/', [CategoryController::class, 'subcategoryUpdate'])
                ->name('update')
                ->middleware('permission:view-categories-escalation');
              Route::patch('/inactive', [CategoryController::class, 'subcategoryInactive'])
                ->name('inactive')
                ->middleware('permission:view-categories-escalation');
              Route::patch('/active', [CategoryController::class, 'subcategoryActive'])
                ->name('active')
                ->middleware('permission:view-categories-escalation');
            });
          });
      });
    });

  // Source routes
  Route::prefix('sources')
    ->name('sources.')
    ->group(function () {
      Route::get('/', [SourceController::class, 'index'])
        ->name('index')
        ->middleware('permission:view-sources-escalation');
      Route::get('/create', [SourceController::class, 'create'])
        ->name('create')
        ->middleware('permission:view-sources-escalation');
      Route::post('/', [SourceController::class, 'store'])
        ->name('store')
        ->middleware('permission:view-sources-escalation');

      // Single source routes
      Route::prefix('{source}')->group(function () {
        Route::get('/', [SourceController::class, 'show'])
          ->name('show')
          ->middleware('permission:view-sources-escalation');
        Route::get('/edit', [SourceController::class, 'edit'])
          ->name('edit')
          ->middleware('permission:view-sources-escalation');
        Route::put('/', [SourceController::class, 'update'])
          ->name('update')
          ->middleware('permission:view-sources-escalation');
        Route::patch('/active', [SourceController::class, 'active'])
          ->name('active')
          ->middleware('permission:view-sources-escalation');
        Route::patch('/deactive', [SourceController::class, 'deactive'])
          ->name('deactive')
          ->middleware('permission:view-sources-escalation');
      });
    });

  // Dashboard routes
  Route::prefix('dashboard')
    ->name('dashboard.')
    ->group(function () {
      Route::get('/', [DashboardController::class, 'index'])
        ->name('index')
        ->middleware('permission:view-dashboard-escalation');
      Route::get('/sub-department/{id}', [DashboardController::class, 'subDepartmentDetails'])
        ->name('sub-department')
        ->middleware('permission:view-dashboard-escalation');
    });

  // Reports routes
  Route::prefix('reports')
    ->name('escalations.reports.')
    ->group(function () {
      Route::get('/', [ReportsController::class, 'index'])
        ->name('index')
        ->middleware('permission:view-reports-escalation');
      Route::get('/sla', [ReportsController::class, 'slaReport'])
        ->name('sla')
        ->middleware('permission:view-reports-escalation');
      Route::get('/productivity', [ReportsController::class, 'productivityReport'])
        ->name('productivity')
        ->middleware('permission:view-reports-escalation');
      Route::get('/sub-category', [ReportsController::class, 'subCategoryReport'])
        ->name('sub-category')
        ->middleware('permission:view-reports-escalation');
      Route::get('/export-excel', [ReportsController::class, 'exportExcel'])
        ->name('export-excel')
        ->middleware('permission:view-reports-escalation');
    });

  // Notification routes
  Route::prefix('notifications')
    ->name('notifications.')
    ->group(function () {
      Route::get('/', [NotificationController::class, 'index'])
        ->name('index')
        ->middleware('permission:view-notifications-escalation');
      Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])
        ->name('mark-read')
        ->middleware('permission:view-notifications-escalation');
      Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])
        ->name('mark-all-read')
        ->middleware('permission:view-notifications-escalation');
      Route::get('/count', [NotificationController::class, 'getUnreadCount'])->name('count');
      Route::get('/recent', [NotificationController::class, 'getRecentUnread'])->name('recent');
    });
});
