<?php


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FontSettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\TeamTypeController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\Admin\PermissionsController;
use App\Http\Controllers\ReportDownloadController;


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
  //Users

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
      Route::get('/', [DepartmentController::class, 'index'])->name('settings.departments.index');
      Route::get('/{id}/sub-department', [DepartmentController::class, 'subDepartment'])->name(
        'settings.departments.subDepartment'
      );
      Route::post('/{id}/sub-department/store', [DepartmentController::class, 'SubDepartmentCreate'])->name(
        'settings.departments.subDepartment.store'
      );
      Route::put('/sub-department/{id}/update', [DepartmentController::class, 'updateSubDepartment'])->name(
        'settings.departments.subDepartment.update'
      );
      Route::post('/store', [DepartmentController::class, 'store'])->name('settings.departments.post');
      Route::post('/{id}/edit', [DepartmentController::class, 'edit'])->name('settings.departments.edit');
      Route::get('/{id}/activate', [DepartmentController::class, 'activate'])->name('settings.departments.activate');
      Route::get('/{id}/deactivate', [DepartmentController::class, 'deactivate'])->name(
        'settings.departments.deactivate'
      );
      Route::get('/{id}/sub-department/activate', [DepartmentController::class, 'activateSub'])->name(
        'settings.departments.activateSub'
      );
      Route::get('/{id}/sub-department/deactivate', [DepartmentController::class, 'deactivateSub'])->name(
        'settings.departments.deactivateSub'
      );
    });

    // Roles Routes
    Route::prefix('roles')->group(function () {
      Route::get('/', [RolesController::class, 'index'])->name('settings.roles');
      Route::post('/store', [RolesController::class, 'store'])->name('roles.store');
      Route::put('/{id}', [RolesController::class, 'update'])->name('roles.update');
      Route::get('/{id}/permissions', [RolesController::class, 'rolePermissions'])->name('roles.permissions');
      Route::post('/{id}/permissions', [RolesController::class, 'updateRolePermissions'])->name(
        'roles.permissions.store'
      );
      Route::get('/{id}/view', [RolesController::class, 'view'])->name('roles.permissions.view');
      Route::get('/permission-role/{id}', [RolesController::class, 'getSubModule'])->name(
        'roles.permissions.sub-module'
      );
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
      Route::get('/', [TeamTypeController::class, 'index'])->name('index');
      Route::get('/create', [TeamTypeController::class, 'create'])->name('create');
      Route::post('/', [TeamTypeController::class, 'store'])->name('store');

      Route::get('/{teamtype}/sub-teams', [TeamTypeController::class, 'getSubTeams'])
      ->name('sub-teams')
      ->where('teamtype', '[0-9]+');

      Route::get('/sub-departments', [TeamTypeController::class, 'getSubDepartments'])
      ->name('sub-departments');

      Route::get('/team-types-by-department', [TeamTypeController::class, 'getTeamTypesByDepartment'])
      ->name('team-types-by-department');

      Route::get('/{teamtype}', [TeamTypeController::class, 'show'])->name('show');
      Route::get('/{teamtype}/edit', [TeamTypeController::class, 'edit'])->name('edit');
      Route::put('/{teamtype}', [TeamTypeController::class, 'update'])->name('update');
      Route::delete('/{teamtype}', [TeamTypeController::class, 'destroy'])->name('destroy');

      // Toggle status route
      Route::patch('/{teamtype}/toggle-status', [TeamTypeController::class, 'toggleStatus'])->name('toggle-status');

      // Sub-Team Types Routes
      Route::prefix('{teamtype}/sub-types')
        ->name('sub-types.')
        ->group(function () {
          Route::get('/create', [TeamTypeController::class, 'createSubType'])->name('create');
          Route::post('/', [TeamTypeController::class, 'storeSubType'])->name('store');
          Route::get('/{subtype}/edit', [TeamTypeController::class, 'editSubType'])->name('edit');
          Route::put('/{subtype}', [TeamTypeController::class, 'updateSubType'])->name('update');
          Route::delete('/{subtype}', [TeamTypeController::class, 'destroySubType'])->name('destroy');

          // Toggle sub team type status route
          Route::patch('/{subtype}/toggle-status', [TeamTypeController::class, 'toggleSubTypeStatus'])->name('toggle-status');
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
Route::post('/font-settings/update', [FontSettingsController::class, 'updateFontSize'])->name('font.update');

require __DIR__ . '/auth.php';
