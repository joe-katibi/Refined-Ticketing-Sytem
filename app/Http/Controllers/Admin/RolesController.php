<?php

namespace App\Http\Controllers\Admin;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Exceptions\RoleAlreadyExists;

class RolesController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */

    protected $dateFormat = 'Y-m-d H : i : s';

    public function index(Request $request)
    {
        $roles = Role::all();
        $permissions = Permission::all();

        return view('settings.roles.index', [
            'roles' => $roles,
            'permissions' => $permissions,
            'permission_modules' => Permission::modules(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('settings.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        try {
            $role = Role::create([
                'name' => $request->input('name'),
                'description' => $request->input('description')
            ]);

            return redirect('settings/roles/' . $role->id . '/permissions')->with(['message' => 'Role Created']);
        } catch (RoleAlreadyExists $e) {
            $role = Role::findByName($request->input('name'));
            if ($role) {
                $role->description = $request->input('description');
                $role->save();
                return redirect('settings/roles/' . $role->id . '/permissions')->with(['message' => 'Role Updated']);
            }
            return redirect()->back()->with(['message' => $e->getMessage(), 'message_type' => 'danger']);
        }
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show()
    {
        return view('settings.show');
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit()
    {
        return view('settings.edit');
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request, $id)
    {

        try {
            /** @var Role $role */
            $role = Role::findById($id);

            $role->name = $request->input('name');
            $role->description = $request->input('description');
            $role->save();

            return redirect('settings/roles/' . $role->id . '/permissions')->with(['message' => 'Role Updated']);
        } catch (RoleAlreadyExists $e) {
            return redirect()->back()->with(['message' => $e->getMessage(), 'message_type' => 'danger']);
        }
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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function rolePermissions(Request $request, $id)
    {
        $permissions = Permission::all();

        /** @var Role $role */
        $role = Role::findById($id);

        /** @var Collection $role_permissions */
        $role_permissions = $role->permissions()->get();

        // print_pre([$permissions , $role , $role_permissions] , true);
        return view('settings.roles.role-permissions', [
            'role' => $role,
            'permission_modules' => Permission::modules(),
            'permissions' => $permissions,
            'role_permissions' => $role_permissions
        ]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateRolePermissions(Request $request, $id)
    {
        // $input = $request->all();
        // print_pre([$id , $input]);

        $module = $request->input('module');
        $sub_module = $request->input('sub_module');

        /** @var array $permissions */
        $permissions = $request->input('permissions') ? $request->input('permissions') : [];

        /** @var Role $role */
        $role = Role::findById($id);

        /** @var Collection $current_permissions */
        $current_permissions = $role->permissions()->get();

        // print_pre([$current_permissions ,$module, $sub_module, $permissions] , true);

        $delete_permissions = $current_permissions->filter(function ($permission) use ($module, $sub_module, $permissions) {
            return $permission->module == $module && $permission->sub_module == $sub_module &&
                !in_array($permission->name, $permissions);
        });

        // print_pre([$module , $sub_module] , true);

        $role->revokePermissionTo($delete_permissions);

        $role->givePermissionTo($permissions);

        return redirect('settings/roles/' . $role->id . '/permissions')
            ->with([
                'message' => 'Role Permissions Updated',
                'module' => $module,
                'sub_module' => $sub_module
            ]);
    }
    public function view(Request $request, $id)
    {
        // Fetch the role by ID
        $role = Role::findOrFail($id);

        // Get all permissions associated with this role (DataTable handles pagination)
        $permissions = $role->permissions()->get();

        // Handle AJAX request for JSON response (if applicable)
        if ($request->ajax()) {
            return response()->json(['role' => $role, 'permissions' => $permissions]);
        }

        // Return the view with the role data and all permissions
        return view('settings.roles.view', compact('role', 'permissions'));
    }

    public function getSubModule($id)
    {
      $subModule = Permission::select('permissions.id','permissions.sub_module')->where('permissions.id',$id)->get();

      return response()->json($subModule);

    }


}
