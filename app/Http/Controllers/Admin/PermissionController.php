<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
      /**
     * Display a listing of the resource.
     * @return Response
     */
    protected $dateFormat = 'Y-m-d H : i : s';

    public function index()
    {
        $permissions = Permission::all();

        return view('settings.permissions.index', [
            'permissions' => $permissions
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('settings.permissions.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show()
    {
        return view('settings.permissions.show');
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit()
    {
        return view('settings.permissions.edit');
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request)
    {
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function permissionModules(Request $request)
    {
        $modules = Permission::modules();

        return response()->json($modules);
    }

    /**
     * @param Request $request
     * @param $module
     * @return \Illuminate\Http\JsonResponse
     */
    public function permissionSubModules(Request $request, $module)
    {
        $sub_modules = Permission::subModules($module);

        return response()->json($sub_modules);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function filterPermissions(Request $request)
    {
        $module = $request->input('module');
        $sub_module = $request->input('sub_module');

        $permissions = Permission::filter($module, $sub_module);

        return response()->json($permissions);
    }
}
