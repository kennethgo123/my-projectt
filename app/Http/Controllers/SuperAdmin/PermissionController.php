<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PermissionController extends Controller
{
    /**
     * Display a listing of the permissions.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $permissions = Permission::all();
        
        return view('super-admin.permissions.index', compact('permissions'));
    }

    /**
     * Store a newly created permission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'module' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->route('super-admin.permissions.index')
                ->withErrors($validator)
                ->withInput();
        }

        // Generate slug from name
        $slug = Str::slug($request->name, '_');
        
        Permission::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'module' => $request->module,
        ]);

        return redirect()->route('super-admin.permissions.index')
            ->with('success', 'Permission created successfully');
    }

    /**
     * Show the form for editing the specified permission.
     *
     * @param  \App\Models\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function edit(Permission $permission)
    {
        return view('super-admin.permissions.edit', compact('permission'));
    }

    /**
     * Update the specified permission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Permission $permission)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'module' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->route('super-admin.permissions.edit', $permission)
                ->withErrors($validator)
                ->withInput();
        }

        // Generate slug from name if name changed
        if ($request->name !== $permission->name) {
            $slug = Str::slug($request->name, '_');
        } else {
            $slug = $permission->slug;
        }

        $permission->update([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'module' => $request->module,
        ]);

        return redirect()->route('super-admin.permissions.index')
            ->with('success', 'Permission updated successfully');
    }

    /**
     * Remove the specified permission.
     *
     * @param  \App\Models\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function destroy(Permission $permission)
    {
        // Detach from all departments
        $permission->departments()->detach();
        $permission->delete();

        return redirect()->route('super-admin.permissions.index')
            ->with('success', 'Permission deleted successfully');
    }
}
