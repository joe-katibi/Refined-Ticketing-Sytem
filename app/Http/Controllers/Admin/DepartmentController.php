<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Department;
use App\Models\SubDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

use function App\Helpers\print_pre;

class DepartmentController extends Controller
{
        /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Fetch all departments with creator relationship
        $departments = Department::with('creator')->get();
        
        // Add sub-department count to each department
        $departments->each(function ($department) {
            $department->sub_departments_count = SubDepartment::where('department_id', $department->id)->count();
        });

        return view('settings.departments.index', compact('departments'));
    }

    /**
     * Update the specified department.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        try {
            $department = Department::findOrFail($id);

            $validatedData = $request->validate([
                'modalAddDepartmentName' => 'required|string|max:255',
                'modalAddDepartmentDescription' => 'required|string|max:255',

            ]);

            $department->update([
                'department_name' => $validatedData['modalAddDepartmentName'],
                'description' => $validatedData['modalAddDepartmentDescription'],
                'edited_by' => Auth::id(),
            ]);

            toast('Department Created successfully','success')->position('top-end');
            return back();
        } catch (\Exception $e) {
            Log::error('Error updating department: ' . $e->getMessage());
            toast('Department Not Created Successfully')->position('top-end');
            return back();
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $dept = new Department();
        try {

            $dept->department_name = $input['modalAddDepartmentName'];
            $dept->description = $input['modalAddDepartmentDescription'];
            $dept->created_by = Auth::user()->id;

            $dept->save();
             toast('Department Created successfully','success')->position('top-end');
            return back();
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            toast('Department Not Created Successfully')->position('top-end');
            return back();
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /**
     * Update the specified department in storage.
     * Handles PUT /departments/{id}
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $input = $request->all();
        $user_id = auth()->user()->id;
        try {
            DB::beginTransaction();

            $editDepartment = Department::where('id', '=', $id)->first();

            if (!$editDepartment) {
                throw new \Exception('Department not found');
            }

            $editDepartment->department_name = $input['modalAddDepartmentName'] ?? $editDepartment->department_name;
            $editDepartment->description = $input['modalAddDepartmentDescription'] ?? $editDepartment->description;
            $editDepartment->edited_by = $user_id;
            $editDepartment->save();

            DB::commit();

            toast('Department Updated Successfully', 'success')->position('top-end');
            return back();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Department Update Error: ' . $e->getMessage());
            toast('Department Update Failed', 'error')->position('top-end');
            return back();
        }
    }
    public function activate($id)
    {
        /** @var Department $department */
        $department = Department::findOrFail($id);

        $department->department_status = 1;
        $department->edited_by = Auth::user()->id;


        $department->save();

        return back();
    }

    public function deactivate($id)
    {
        /** @var Department $department */

        $department = Department::findOrFail($id);

        $department->department_status = 0;
        $department->edited_by = Auth::user()->id;

        $department->save();

        return back();
    }

    public function subDepartment(Request $request,$id)
    {
             // Start with a query for SubGeneralIssue, filtering by `general_id`
      $query = SubDepartment::where('department_id', $id);

      // If a search term is provided, apply a search filter
      if ($request->has('search') && $request->search != '') {
        $search = $request->search;
        $query->where('sub_department_name', 'like', '%' . $search . '%'); // Adjust field 'issue_name' as per your table structure

      }

      // Get the per-page value from the request or set a default value (10 in this case)
      $perPage = $request->get('DataTables_Table_0_length', 10);

      // Execute the query and paginate results
      $sub = $query->paginate($perPage);

      $tittle = Department::select('department_name')->where('id',$id)->first();

      // If the request is AJAX, return JSON response for dynamic updates
      if ($request->ajax()) {
        return response()->json(['sub' => $sub]);
      }

      return view('settings.departments.sub_department', compact('sub','tittle'));

    }

    public function activateSub($id)
    {
        /** @var SubGeneralIssue $sub */
        $sub = SubDepartment::findOrFail($id);
        $sub->sub_department_status = 1;
        $sub->save();
        return back();
    }

    public function deactivateSub($id)
    {
        /** @var SubGeneralIssue $sub */
        $sub = SubDepartment::findOrFail($id);
        $sub->sub_department_status = 0;
        $sub->save();

        return back();
    }
    public function SubDepartmentCreate(Request $request, $id)
    {
      $input = $request->all();



      try {

        DB::beginTransaction();
        $subDepartment = new SubDepartment();
        $subDepartment->sub_department_name= isset($input['AddSubDepartmentName']) ? $input['AddSubDepartmentName']:"";
        $subDepartment->sub_department_status = isset($input['status']) ? $input['status']:"";
        $subDepartment->department_id = isset($input['departemnt_id']) ? $input['departemnt_id']:"";
        $subDepartment->created_by = Auth::user()->id;

        $subDepartment->save();

        log::channel('subDepartment')->info('subDepartment Created : ------> ', ['200' , $subDepartment->toArray() ] );

        DB::commit();


        return redirect('settings/departments/'.$subDepartment->department_id.'/sub-department')
        ->with('success', 'Sub Department created successfully.');


      } catch (\Throwable $e) {
        DB::rollBack();
        Log::info($e->getMessage() );
        throw $e;
       }

    }

    /**
     * Update the specified sub department in storage.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateSubDepartment(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $subDepartment = SubDepartment::findOrFail($id);

            $validatedData = $request->validate([
                'sub_department_name' => 'required|string|max:255',
                // Note: sub_department_description field doesn't exist in database schema
            ]);

            $subDepartment->update([
                'sub_department_name' => $validatedData['sub_department_name'],
                'edited_by' => Auth::id(),
            ]);

            DB::commit();

            toast('Sub Department updated successfully', 'success')->position('top-end');
            return back();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating sub department: ' . $e->getMessage());
            toast('Sub Department update failed', 'error')->position('top-end');
            return back();
        }
    }

}
