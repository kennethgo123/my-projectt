<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Permission;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    /**
     * Display a listing of departments.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $departments = Department::with('permissions')->get();
        $permissions = Permission::all();
        
        return view('super-admin.departments.index', compact('departments', 'permissions'));
    }

    /**
     * Store a newly created department.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:departments',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return redirect()->route('super-admin.departments.index')
                ->withErrors($validator)
                ->withInput();
        }

        $department = Department::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        if ($request->has('permissions')) {
            $department->permissions()->attach($request->permissions);
        }

        return redirect()->route('super-admin.departments.index')
            ->with('success', 'Department created successfully');
    }

    /**
     * Show the form for editing the specified department.
     *
     * @param  \App\Models\Department  $department
     * @return \Illuminate\Http\Response
     */
    public function edit(Department $department)
    {
        $permissions = Permission::all();
        $department->load('permissions');
        
        return view('super-admin.departments.edit', compact('department', 'permissions'));
    }

    /**
     * Update the specified department.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Department  $department
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Department $department)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return redirect()->route('super-admin.departments.edit', $department)
                ->withErrors($validator)
                ->withInput();
        }

        $department->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        $department->permissions()->sync($request->permissions ?? []);

        return redirect()->route('super-admin.departments.index')
            ->with('success', 'Department updated successfully');
    }

    /**
     * Remove the specified department.
     *
     * @param  \App\Models\Department  $department
     * @return \Illuminate\Http\Response
     */
    public function destroy(Department $department)
    {
        $department->delete();

        return redirect()->route('super-admin.departments.index')
            ->with('success', 'Department deleted successfully');
    }
}
