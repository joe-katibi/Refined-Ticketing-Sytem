<?php

use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\PermissionsController;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\FontSettingsController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportDownloadController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamTypeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});

// First-time password change routes
Route::middleware('auth')->group(function () {
    Route::get('/password/first-time', [App\Http\Controllers\Auth\FirstTimePasswordController::class, 'show'])
        ->name('password.first-time');
    Route::post('/password/first-time', [App\Http\Controllers\Auth\FirstTimePasswordController::class, 'update'])
        ->name('password.update-first-time');
});

// Load Module Routes
if (! function_exists('require_module_route')) {
    function require_module_route(string $module, string $relativePath)
    {
        if (! function_exists('module_path')) {
            return;
        }

        try {
            $path = module_path($module, $relativePath);
        } catch (\Throwable $e) {
            return;
        }

        if (file_exists($path)) {
            require $path;
        }
    }
}

Route::middleware(['auth', 'verified'])->group(function () {
    // Include module routes
    require_module_route('Appointment', 'routes/web.php');
    require_module_route('Outages', 'routes/web.php');
    require_module_route('Escalations', 'routes/web.php');
});

Route::middleware(['auth', 'check.first.login'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('dashboard');
    Route::get('/user-dashboard', [UserController::class, 'userDashboard'])->name('user.dashboard');
    // Users

    Route::prefix('settings')->group(function () {
        // Users Routes
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('settings.users')->middleware('permission:view-list-user');
            Route::get('/create', [UserController::class, 'create'])->name('settings.create')->middleware('permission:view-create-user');
            Route::post('/', [UserController::class, 'store'])->name('settings.store')->middleware('permission:view-create-user');
            Route::get('/{id}', [UserController::class, 'show'])->name('settings.users.show')->middleware('permission:view-view-user');
            Route::put('/{id}/update', [UserController::class, 'update'])->name('users.update')->middleware('permission:view-edit-user');
            Route::get('/{id}/edit', [UserController::class, 'edit'])->name('settings.users.edit')->middleware('permission:view-edit-user');
            Route::get('/{id}/newUser', [UserController::class, 'newUser'])->name('settings.users.newUser')->middleware('permission:view-view-user');
            Route::get('/{id}/profile', [UserController::class, 'profile'])->name('settings.users.profile')->middleware('permission:view-view-user');
            Route::get('/{id}/view', [UserController::class, 'view'])->name('settings.users.view')->middleware('permission:view-view-user');
            Route::get('/{id}/activate', [UserController::class, 'activate'])->name('settings.users.activate')->middleware('permission:view-edit-user-status');
            Route::get('/{id}/deactivate', [UserController::class, 'deactivate'])->name('settings.users.deactivate')->middleware('permission:view-edit-user-status');
            Route::post('/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('settings.users.toggle-status')->middleware('permission:view-edit-user-status');
            Route::get('/{id}/reset-password', [UserController::class, 'requestPasswordReset'])->name(
                'settings.users.password.reset'
            )->middleware('permission:view-edit-user');
            Route::post('/{id}/permissions', [UserController::class, 'updateUserPermissions'])->name(
                'user.permissions.store'
            )->middleware('permission:view-edit-user');
        });

        // Departments Routes
        Route::prefix('departments')->group(function () {
            Route::get('/', [DepartmentController::class, 'index'])->name('settings.departments.index')->middleware('permission:view-user-department-menu');
            Route::get('/{id}/sub-department', [DepartmentController::class, 'subDepartment'])->name(
                'settings.departments.subDepartment'
            )->middleware('permission:view-user-department-menu');
            Route::post('/{id}/sub-department/store', [DepartmentController::class, 'SubDepartmentCreate'])->name(
                'settings.departments.subDepartment.store'
            )->middleware('permission:view-user-create-sub-department');
            Route::put('/sub-department/{id}/update', [DepartmentController::class, 'updateSubDepartment'])->name(
                'settings.departments.subDepartment.update'
            )->middleware('permission:view-user-edit-Sub-department');
            Route::post('/store', [DepartmentController::class, 'store'])->name('settings.departments.post')->middleware('permission:view-user-create-department');
            Route::post('/{id}/edit', [DepartmentController::class, 'edit'])->name('settings.departments.edit')->middleware('permission:view-user-edit-department');
            Route::get('/{id}/activate', [DepartmentController::class, 'activate'])->name('settings.departments.activate')->middleware('permission:view-user-edit-department');
            Route::get('/{id}/deactivate', [DepartmentController::class, 'deactivate'])->name(
                'settings.departments.deactivate'
            )->middleware('permission:view-user-edit-department');
            Route::get('/{id}/sub-department/activate', [DepartmentController::class, 'activateSub'])->name(
                'settings.departments.activateSub'
            )->middleware('permission:view-user-edit-Sub-department');
            Route::get('/{id}/sub-department/deactivate', [DepartmentController::class, 'deactivateSub'])->name(
                'settings.departments.deactivateSub'
            )->middleware('permission:view-user-edit-Sub-department');
        });

        // Roles Routes
        Route::prefix('roles')->group(function () {
            Route::get('/', [RolesController::class, 'index'])->name('settings.roles')->middleware('permission:view-user-roles-menu');
            Route::post('/store', [RolesController::class, 'store'])->name('roles.store')->middleware('permission:view-user-role');
            Route::put('/{id}', [RolesController::class, 'update'])->name('roles.update')->middleware('permission:view-user-edit-role');
            Route::get('/{id}/permissions', [RolesController::class, 'rolePermissions'])->name('roles.permissions')->middleware('permission:view-user-roles-menu');
            Route::post('/{id}/permissions', [RolesController::class, 'updateRolePermissions'])->name(
                'roles.permissions.store'
            )->middleware('permission:view-user-role-permissions');
            Route::get('/{id}/view', [RolesController::class, 'view'])->name('roles.permissions.view')->middleware('permission:view-user-roles-menu');
            Route::get('/permission-role/{id}', [RolesController::class, 'getSubModule'])->name(
                'roles.permissions.sub-module'
            )->middleware('permission:view-user-roles-menu');
        });

        // Permissions Routes
        Route::prefix('permissions')->group(function () {
            Route::get('/', [PermissionsController::class, 'index'])->name('settings.permissions');
            Route::get('/filter', [PermissionsController::class, 'filterPermissions'])->name('permissions.filter');
            Route::get('/modules', [PermissionsController::class, 'permissionModules'])->name('permissions.modules');
            Route::get('/sub-modules/{module}', [PermissionsController::class, 'getSubModules'])->name(
                'permissions.sub-modules'
            );
            Route::get('/modules/{module}/sub-modules', [PermissionsController::class, 'permissionSubModules'])->name(
                'permissions.modules.sub-modules'
            );
            Route::get('/modules/{module}/sub-modules/{submodule}', [
                PermissionsController::class,
                'getSubModulePermissions',
            ])->name('permissions.sub-modules.permissions');
        });
    });
});

// Team Types Routes with Sub-Types
Route::middleware('auth')->group(function () {
    Route::prefix('teamtypes')
        ->name('teamtypes.')
        ->group(function () {
            // Main Team Types Routes
            Route::get('/', [TeamTypeController::class, 'index'])->name('index')->middleware('permission:view-user-view-team-types');
            Route::get('/create', [TeamTypeController::class, 'create'])->name('create')->middleware('permission:view-user-create-team-types');
            Route::post('/', [TeamTypeController::class, 'store'])->name('store')->middleware('permission:view-user-create-team-types');

            Route::get('/{teamtype}/sub-teams', [TeamTypeController::class, 'getSubTeams'])
                ->name('sub-teams')
                ->where('teamtype', '[0-9]+');

            Route::get('/sub-departments', [TeamTypeController::class, 'getSubDepartments'])
                ->name('sub-departments');

            Route::get('/team-types-by-department', [TeamTypeController::class, 'getTeamTypesByDepartment'])
                ->name('team-types-by-department');

            Route::get('/{teamtype}', [TeamTypeController::class, 'show'])->name('show')->middleware('permission:view-user-view-team-types');
            Route::get('/{teamtype}/edit', [TeamTypeController::class, 'edit'])->name('edit')->middleware('permission:view-user-Edit-team-types');
            Route::put('/{teamtype}', [TeamTypeController::class, 'update'])->name('update')->middleware('permission:view-user-Edit-team-types');
            Route::delete('/{teamtype}', [TeamTypeController::class, 'destroy'])->name('destroy')->middleware('permission:view-user-Edit-team-types');

            // Toggle status route
            Route::patch('/{teamtype}/toggle-status', [TeamTypeController::class, 'toggleStatus'])->name('toggle-status')->middleware('permission:view-user-Edit-team-types');

            // Sub-Team Types Routes
            Route::prefix('{teamtype}/sub-types')
                ->name('sub-types.')
                ->group(function () {
                    Route::get('/create', [TeamTypeController::class, 'createSubType'])->name('create')->middleware('permission:view-user-create-sub-team-types');
                    Route::post('/', [TeamTypeController::class, 'storeSubType'])->name('store')->middleware('permission:view-user-create-sub-team-types');
                    Route::get('/{subtype}/edit', [TeamTypeController::class, 'editSubType'])->name('edit')->middleware('permission:view-user-edit-sub-team-types');
                    Route::put('/{subtype}', [TeamTypeController::class, 'updateSubType'])->name('update')->middleware('permission:view-user-edit-sub-team-types');
                    Route::delete('/{subtype}', [TeamTypeController::class, 'destroySubType'])->name('destroy')->middleware('permission:view-user-edit-sub-team-types');

                    // Toggle sub team type status route
                    Route::patch('/{subtype}/toggle-status', [TeamTypeController::class, 'toggleSubTypeStatus'])->name('toggle-status')->middleware('permission:view-user-edit-sub-team-types');
                });
        });

    // Partners Routes
    Route::resource('partners', PartnerController::class)->names('partners');

    // Teams Management Routes
    Route::prefix('team')
        ->name('team.')
        ->group(function () {
            Route::get('/', [TeamController::class, 'index'])->name('index');
            Route::get('/create', [TeamController::class, 'create'])->name('create');
            Route::post('/', [TeamController::class, 'store'])->name('store');
            Route::get('/{team}/edit', [TeamController::class, 'edit'])->name('edit');
            Route::put('/{team}', [TeamController::class, 'update'])->name('update');
            Route::delete('/{team}', [TeamController::class, 'destroy'])->name('destroy');
        });

    // API route for teams dropdown
    Route::get('/teams', [TeamController::class, 'getAllTeams']);
});

// Report Downloads Routes
Route::middleware('auth')->prefix('report-downloads')->name('report-downloads.')->group(function () {
    Route::get('/', [ReportDownloadController::class, 'index'])->name('index');
    Route::get('/data', [ReportDownloadController::class, 'getData'])->name('data');
    Route::get('/{id}/download', [ReportDownloadController::class, 'download'])->name('download');
    Route::get('/{id}/error', [ReportDownloadController::class, 'getError'])->name('error');
    Route::delete('/{id}', [ReportDownloadController::class, 'destroy'])->name('destroy');
    Route::get('/unread-count', [ReportDownloadController::class, 'getUnreadCount'])->name('unread-count');
});

// Font Settings Route
Route::middleware('auth')->post('/font-settings/update', [FontSettingsController::class, 'updateFontSize'])->name('font.update');

// FIFO dispatch console — shared across escalation/appointment/outage queues.
Route::middleware(['auth', 'verified'])->prefix('fifo/{module}')->name('fifo.')->group(function () {
    Route::get('/', [App\Http\Controllers\FifoQueueController::class, 'index'])->name('index');
    Route::post('/assign-next', [App\Http\Controllers\FifoQueueController::class, 'assignNext'])->name('assign-next');
    Route::post('/bulk-assign', [App\Http\Controllers\FifoQueueController::class, 'bulkAssign'])->name('bulk-assign');
    Route::post('/agent-availability', [App\Http\Controllers\FifoQueueController::class, 'setAgentAvailability'])->name('agent-availability');
});

require __DIR__.'/auth.php';
