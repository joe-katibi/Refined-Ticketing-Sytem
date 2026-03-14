<?php

namespace App\Http\Controllers\Admin;

use DB;
use App\Models\Role;
use App\Models\User;
use App\Models\Country;
use App\Models\Service;
use App\Models\Category;
use App\Models\Department;
use App\Models\Permission;
use App\Models\TeamType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class UserController extends Controller
{
  use SendsPasswordResetEmails;
  protected $dateFormat = 'Y-m-d H : i : s';

  public function __construct()
  {
    $this->middleware('permission:view-list-user')->only(['index']);
    $this->middleware('permission:view-create-user')->only(['create', 'store']);
    $this->middleware('permission:view-view-user')->only(['show', 'view']);
    $this->middleware('permission:view-edit-user')->only(['edit', 'update']);
    $this->middleware('permission:view-edit-user-status')->only(['activate', 'deactivate', 'toggleStatus']);
    $this->middleware('permission:view-dashboard-user')->only(['userDashboard']);
  }

  /**
   * Display a listing of the resource.
   * @return Response
   */
  public function index(Request $request)
  {
    $query = User::with('roles'); // Eager-load the 'roles' relationship

    if ($request->has('search') && $request->search != '') {
      $query
        ->where('name', 'like', '%' . $request->search . '%')
        ->orWhere('email', 'like', '%' . $request->search . '%');
    }

    // Get the per-page value from the request or set a default (15 in this case)
    $perPage = $request->get('per_page', 15);

    // Ensure we're using a reasonable pagination size
    $users = $query->paginate($perPage);

    if ($request->ajax()) {
      return response()->json(['users' => $users]);
    }

    $country = Country::where('country_status', 1)->get();
    $service = Service::where('service_status', 1)->get();
    $department = Department::where('department_status', 1)->get();
    $roles = Role::all();
    $teamTypes = TeamType::where('status', 'Active')->get(); // Fetch active team types

    return view('settings.users.index', compact('users', 'country', 'service', 'department', 'roles', 'teamTypes'));
  }

  /**
   * Display the user dashboard with comprehensive metrics
   * @return Response
   */
  public function userDashboard()
  {
    // Total users count
    $totalUsers = User::count();

    // Active and inactive users
    $activeUsers = User::where('user_status', 'Active')->count();
    $inactiveUsers = User::where('user_status', 'Inactive')->count();

    // Users per department with department names
    $usersPerDepartment = User::select('departments.department_name', \DB::raw('count(*) as user_count'))
      ->join('departments', 'users.department_id', '=', 'departments.id')
      ->groupBy('departments.id', 'departments.department_name')
      ->orderBy('user_count', 'desc')
      ->get();

    // Users per sub department with sub department names
    $usersPerSubDepartment = User::select('sub_departments.sub_department_name', \DB::raw('count(*) as user_count'))
      ->join('sub_departments', 'users.sub_department_id', '=', 'sub_departments.id')
      ->whereNotNull('users.sub_department_id')
      ->groupBy('sub_departments.id', 'sub_departments.sub_department_name')
      ->orderBy('user_count', 'desc')
      ->get();

    // Users per role
    $usersPerRole = User::select('roles.name as role_name', \DB::raw('count(*) as user_count'))
      ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
      ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
      ->groupBy('roles.id', 'roles.name')
      ->orderBy('user_count', 'desc')
      ->get();

    // Users per team type (with error handling)
    try {
      $usersPerTeamType = User::select('team_types.type_name', \DB::raw('count(*) as user_count'))
        ->join('team_types', 'users.team_type_id', '=', 'team_types.id')
        ->whereNotNull('users.team_type_id')
        ->groupBy('team_types.id', 'team_types.type_name')
        ->orderBy('user_count', 'desc')
        ->get();
    } catch (\Exception $e) {
      $usersPerTeamType = collect();
    }

    // Users per sub team type (with error handling)
    try {
      $usersPerSubTeamType = User::select('sub_team_types.sub_type_name', \DB::raw('count(*) as user_count'))
        ->join('sub_team_types', 'users.sub_team_type_id', '=', 'sub_team_types.id')
        ->whereNotNull('users.sub_team_type_id')
        ->groupBy('sub_team_types.id', 'sub_team_types.sub_type_name')
        ->orderBy('user_count', 'desc')
        ->get();
    } catch (\Exception $e) {
      $usersPerSubTeamType = collect();
    }

    // Recent users (last 30 days)
    $recentUsers = User::where('created_at', '>=', now()->subDays(30))->count();

    // Users with supervisors (with error handling)
    try {
      $usersWithSupervisors = User::whereNotNull('supervisor_id')->count();
    } catch (\Exception $e) {
      // If supervisor_id column doesn't exist, set to 0
      $usersWithSupervisors = 0;
    }

    // Department with most users
    $topDepartment = $usersPerDepartment->first();

    // Role with most users
    $topRole = $usersPerRole->first();

    // Status distribution for chart
    $statusDistribution = [
      [
        'status' => 'Active',
        'count' => $activeUsers,
        'percentage' => $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100, 1) : 0,
      ],
      [
        'status' => 'Inactive',
        'count' => $inactiveUsers,
        'percentage' => $totalUsers > 0 ? round(($inactiveUsers / $totalUsers) * 100, 1) : 0,
      ],
    ];

    return view(
      'settings.users.dashboard',
      compact(
        'totalUsers',
        'activeUsers',
        'inactiveUsers',
        'usersPerDepartment',
        'usersPerSubDepartment',
        'usersPerRole',
        'usersPerTeamType',
        'usersPerSubTeamType',
        'recentUsers',
        'usersWithSupervisors',
        'topDepartment',
        'topRole',
        'statusDistribution'
      )
    );
  }

  /**
   * Show the form for creating a new resource.
   * @return Response
   */
  public function create()
  {
    $roles = Role::all();
    $department = Department::all();

    return view('settings.users.create', [
      'roles' => $roles,
      'department' => $department,
      'user' => new User(),
    ]);
  }

  /**
   * Store a newly created resource in storage.
   * @param Request $request
   * @return \Illuminate\Http\RedirectResponse|Response
   */
  public function store(Request $request)
  {
    // Log the incoming request data for debugging
    \Log::info('User Creation Request Data:', $request->all());
    \Log::info('Request keys:', array_keys($request->all()));

    $validator = Validator::make($request->all(), [
      'name' => 'required|string|max:255',
      'email' => 'required|email|unique:users,email',
      'username' => 'required|string|max:50|unique:users,username',
      'password' => 'required|string|min:8',
      'department_id' => 'required|exists:departments,id',
      'sub_department_id' => 'nullable|exists:sub_departments,id',
      'status' => 'required|in:Active,Inactive,1,0',
      'roles' => 'required|array',
      'roles.*' => 'exists:roles,id',
      'team_type_id' => 'nullable|exists:team_types,id',
      'sub_team_type_id' => 'nullable|exists:sub_team_types,id',
      'phone' => 'nullable|string|max:20',
      'supervisor_id' => 'nullable|exists:users,id',
    ]);

    if ($validator->fails()) {
      \Log::error('Validation failed', ['errors' => $validator->errors()]);
      \Log::error('Validation failed details', ['error_messages' => $validator->errors()->all()]);
      \Log::error('Validation failed for fields', ['error_fields' => $validator->errors()->keys()]);
      return redirect()
        ->back()
        ->withErrors($validator)
        ->withInput();
    }

    try {
      DB::beginTransaction();
      \Log::info('Starting user creation transaction');

      // Convert status value to the correct format (1 for Active, 0 for Inactive)
      $status = $request->input('status');
      \Log::info('Status value from request:', ['status' => $status]);
      if ($status === '1' || $status === 1 || $status === 'Active') {
        $status = 1; // Store as integer 1 for Active
      } elseif ($status === '0' || $status === 0 || $status === 'Inactive') {
        $status = 0; // Store as integer 0 for Inactive
      }
      \Log::info('Converted status value:', ['status' => $status]);

      // Log the data being used to create the user
      $userData = [
        'name' => $request->input('name'),
        'username' => $request->input('username'),
        'email' => $request->input('email'),
        'user_status' => $status,
        'department_id' => $request->input('department_id'),
        'sub_department_id' => $request->input('sub_department_id'),
        'team_type_id' => $request->input('team_type_id'),
        'sub_team_type_id' => $request->input('sub_team_type_id'),
        'phone' => $request->input('phone'),
        'created_by' => Auth::id(),
      ];

      // Log each field individually for debugging
      foreach ($userData as $field => $value) {
        \Log::info("User field {$field}:", ['value' => $value]);
      }

      \Log::info('Creating user with data:', $userData);

      // Create new user
      $user = new User($userData);
      $user->password = Hash::make($request->input('password'));
      $user->is_first_login = true; // Mark as first-time login

      try {
        \Log::info('Attempting to save user');
        $user->save();
        \Log::info('User saved successfully', ['user_id' => $user->id]);
      } catch (\Exception $saveException) {
        \Log::error('Error saving user:', ['message' => $saveException->getMessage()]);
        \Log::error('Error saving user trace:', ['trace' => $saveException->getTraceAsString()]);
        throw $saveException; // Re-throw to be caught by the outer catch block
      }

      // Assign roles
      $roles = $request->input('roles');
      \Log::info('Roles from request:', ['roles' => $roles]);

      if (!empty($roles)) {
        try {
          \Log::info('Fetching role names for IDs:', ['role_ids' => $roles]);
          $roleNames = Role::whereIn('id', $roles)
            ->pluck('name')
            ->toArray();
          \Log::info('Found role names:', ['role_names' => $roleNames]);

          \Log::info('Syncing roles for user', ['user_id' => $user->id]);
          $user->syncRoles($roleNames);
          \Log::info('Assigned roles to user', ['user_id' => $user->id, 'roles' => $roleNames]);
        } catch (\Exception $roleException) {
          \Log::error('Error assigning roles:', ['message' => $roleException->getMessage()]);
          \Log::error('Error assigning roles trace:', ['trace' => $roleException->getTraceAsString()]);
          throw $roleException; // Re-throw to be caught by the outer catch block
        }
      } else {
        \Log::warning('No roles provided for user', ['user_id' => $user->id]);
      }

      \Log::info('Committing transaction');
      DB::commit();
      \Log::info('Transaction committed successfully');

      return redirect()
        ->route('settings.users')
        ->with('success', 'User created successfully');
    } catch (\Exception $e) {
      \Log::error('Exception caught in user creation');
      DB::rollBack();
      \Log::error('Transaction rolled back');
      \Log::error('Error creating user: ' . $e->getMessage());
      \Log::error('Error code: ' . $e->getCode());
      \Log::error('Error file: ' . $e->getFile() . ' at line ' . $e->getLine());
      \Log::error('Error trace: ' . $e->getTraceAsString());

      // Check if it's a database exception
      if ($e instanceof \Illuminate\Database\QueryException) {
        \Log::error('SQL Error: ' . $e->getSql());
        \Log::error('SQL Bindings: ', $e->getBindings());
      }

      return redirect()
        ->back()
        ->withInput()
        ->with('error', 'An error occurred while creating the user: ' . $e->getMessage());
    }
  }

  /**
   * Show the specified resource.
   * @return \Illuminate\Contracts\View\Factory|Response|\Illuminate\View\View
   */
  public function show()
  {
    /** @var User $user */
    // $user = User::findOrFail($id);

    $user = User::select(
      'users.id',
      'users.name',
      'users.username',
      'users.email',
      'users.department_id',
      'users.user_status',
      'users.created_at',
      'model_has_roles.model_id'
    )
      ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
      ->where('users.id', '=', Auth::user()->id)
      ->first();

    if ($user->roles()) {
      $user->roles = $user
        ->roles()
        ->get()
        ->pluck('name');
    } else {
      $user->roles = new Collection();
    }

    $permissions = Permission::all();
    $department = Department::all();

    // Extract category IDs for use in the select input
    $selectedCategoryIds = $userCategory->pluck('category_id')->toArray();

    return view('settings.users.profile', [
      'user' => $user,
      'department' => $department,
      'roles' => Role::all(),
      'permission_modules' => Permission::modules(),
      'permissions' => $permissions,
      'user_permissions' => $user->permissions()->get(),
    ]);
  }

  /**
   * Show the form for editing the specified resource.
   * @param $id
   * @return \Illuminate\Contracts\View\Factory|Response|\Illuminate\View\View
   */
  public function edit($id)
  {
    /** @var User $user */
    // $user = User::findOrFail($id);

    $user = User::select(
      'users.id',
      'users.name',
      'users.username',
      'users.email',
      'users.department_id',
      'users.user_status',
      'users.created_at',
      'model_has_roles.model_id'
    )
      ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
      ->where('users.id', '=', $id)
      ->first();

    if ($user->roles()) {
      $user->roles = $user
        ->roles()
        ->get()
        ->pluck('name');
    } else {
      $user->roles = new Collection();
    }

    $permissions = Permission::all();
    $userRoles = Role::all();
    $department = Department::all();

    return view('settings.users.edit', [
      'user' => $user,
      'department' => $department,
      'roles' => $userRoles,
      'permission_modules' => Permission::modules(),
      'permissions' => $permissions,
      'user_permissions' => $user->permissions()->get(),
    ]);
  }

  public function newUser($id)
  {
    $user = User::select(
      'users.id',
      'users.name',
      'users.username',
      'users.email',
      'users.department_id',
      'users.user_status',
      'users.created_at'
    )
      ->where('users.id', '=', $id)
      ->first();

    if ($user) {
      if ($user->roles()) {
        $user->roles = $user
          ->roles()
          ->get()
          ->pluck('name');
      } else {
        $user->roles = new Collection();
      }
    } else {
      // Handle the case where $user is null
    }

    $permissions = Permission::all();
    $department = Department::all();

    $userPermissions = [];
    if ($user) {
      $userPermissions = $user->permissions()->get();
    }
    return view('settings.users.newUser', [
      'user' => $user,
      'department' => $department,
      'roles' => Role::all(),
      'permission_modules' => Permission::modules(),
      'permissions' => $permissions,
      'user_permissions' => $userPermissions,
    ]);
  }

  /**
   * Update the specified resource in storage.
   * @param Request $request
   * @return \Illuminate\Http\RedirectResponse|Response
   */
  public function update(Request $request, $id)
  {
    $user = User::findOrFail($id);
    $user->name = $request->input('modalEditUserFirstName');
    $user->username = $request->input('modalEditUserName');
    $user->email = $request->input('modalEditUserEmail');
    $user->user_status = $request->input('modalEditUserStatus');
    $user->department_id = $request->input('modalEditUserDepartment');
    $user->sub_department_id = $request->input('modalEditUserSubDepartment');
    $user->phone = $request->input('modalEditUserPhone');
    $user->team_type_id = $request->input('modalEditUserTeamType');
    $user->sub_team_type_id = $request->input('modalEditUserTeamTypeSub');
    $user->edited_by = Auth::user()->id;
    $user->save();

    // Get the role ID from the form
    $roleId = $request->input('modalEditUserRoles');

    // Find the role by ID
    if ($roleId) {
      $role = Role::find($roleId);
      if ($role) {
        $user->syncRoles([$role->name]);
      }
    } else {
      // If no role is selected, remove all roles
      $user->syncRoles([]);
    }

    toast('User updated', 'success')->position('top-end');
    return redirect()->route('settings.users');
  }

  /**
   * Remove the specified resource from storage.
   * @return Response
   */
  public function destroy()
  {
  }

  /**
   * @param Request $request
   * @param $id
   * @return \Illuminate\Http\RedirectResponse
   */
  public function updateUserPermissions(Request $request, $id)
  {
    $module = $request->input('module');
    $sub_module = $request->input('sub_module');

    /** @var array $permissions */
    $permissions = $request->input('permissions');

    if ($permissions == null) {
      $permissions = [];
    }
    /** @var User $user */
    $user = User::find($id);

    /** @var Collection $current_permissions */
    $current_permissions = $user->permissions()->get();

    $delete_permissions = $current_permissions->filter(function ($permission) use ($module, $sub_module, $permissions) {
      return $permission->module == $module &&
        $permission->sub_module == $sub_module &&
        !in_array($permission->name, $permissions);
    });

    $user->revokePermissionTo($delete_permissions);

    $user->givePermissionTo($permissions);

    return redirect('system/general/users/' . $user->id . '/edit')->with([
      'message' => 'User Permissions Updated',
      'module' => $module,
      'sub_module' => $sub_module,
    ]);
  }

  /**
   * @param Request $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function requestPasswordReset(Request $request, $id)
  {
    $user = User::find($id);

    if (!$user->email) {
      return redirect('system/general/users/' . $id . '/edit')->with([
        'message' => 'The user does not have an email. Add an email to proceed.',
        'message_type' => 'error',
      ]);
    }
    $response = Password::sendResetLink(['email' => $user->email]);

    return redirect('system/general/users/' . $id . '/edit')->with([
      'message' => 'Password reset email sent to user.',
    ]);
  }

  public function activate($id)
  {
    /** @var User $user */
    $user = User::findOrFail($id);

    $user->user_status = 1;
    $user->edited_by = Auth::user()->id;

    $user->save();

    return back();
  }

  public function deactivate($id)
  {
    /** @var User $user */

    $user = User::findOrFail($id);

    $user->user_status = 0;
    $user->edited_by = Auth::user()->id;

    $user->save();

    // Delete existing user categories
    UserCategory::where('user_id', $id)->delete();

    return back();
  }

  /**
   * Toggle user status (activate/deactivate) via AJAX
   * @param Request $request
   * @param $id
   * @return \Illuminate\Http\JsonResponse
   */
  public function toggleStatus(Request $request, $id)
  {
    try {
      $user = User::findOrFail($id);

      // Get the new status from the request
      $newStatus = $request->input('status');

      // Update the user status using the correct field name
      $user->user_status = $newStatus;
      $user->edited_by = Auth::user()->id;
      $user->save();

      // If deactivating, delete user categories like in the deactivate method
      if ($newStatus == 0) {
        UserCategory::where('user_id', $id)->delete();
      }

      return response()->json([
        'success' => true,
        'message' => $newStatus == 1 ? 'User activated successfully' : 'User deactivated successfully',
      ]);
    } catch (\Exception $e) {
      return response()->json(
        [
          'success' => false,
          'message' => 'Error updating user status: ' . $e->getMessage(),
        ],
        500
      );
    }
  }

  public function online_users()
  {
    $data['users'] = User::all();
    return view('system::users.online_status')->with($data);
  }

  public function user_department()
  {
    $data['user'] = User::where('id', '=', Auth::user()->id)->first();
    $data['departments'] = Department::all();
    return view('auth.department_check')->with($data);
  }

  public function user_department_save(Request $request, $id)
  {
    $input = $request->all();
    $user = user::find($id);
    $user->department = $input['department'];
    $user->update();
    return redirect('home');
  }

  /**
   * Get sub departments for a specific department (AJAX endpoint)
   * @param int $departmentId
   * @return \Illuminate\Http\JsonResponse
   */
  public function getSubDepartments($departmentId)
  {
    try {
      $subDepartments = \App\Models\SubDepartment::where('department_id', $departmentId)
        ->where('sub_department_status', 1)
        ->select('id', 'sub_department_name')
        ->get();

      return response()->json($subDepartments);
    } catch (\Exception $e) {
      \Log::error('Error fetching sub departments: ' . $e->getMessage());
      return response()->json([], 500);
    }
  }

  /**
   * Show the specified resource.
   * @return \Illuminate\Contracts\View\Factory|Response|\Illuminate\View\View
   */
  public function view($id)
  {
    /** @var User $user */
    // $user = User::findOrFail($id);

    $user = User::select(
      'users.id',
      'users.name',
      'users.username',
      'users.email',
      'users.department_id',
      'users.user_status',
      'users.created_at',
      'users.position',
      'model_has_roles.model_id'
    )
      ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
      ->where('users.id', '=', $id)
      ->first();

    if ($user->roles()) {
      $user->roles = $user
        ->roles()
        ->get()
        ->pluck('name');
    } else {
      $user->roles = new Collection();
    }

    $permissions = Permission::all();
    $department = Department::all();

    return view('settings.users.view', [
      'user' => $user,
      'department' => $department,
      'roles' => Role::all(),
      'permission_modules' => Permission::modules(),
      'permissions' => $permissions,
      'user_permissions' => $user->permissions()->get(),
    ]);
  }
}
