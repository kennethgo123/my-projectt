<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Support\Facades\Validator;

class UserPermissionController extends Controller
{
    /**
     * Display the user permission management page.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get admin users with department information
        $users = User::with(['departments', 'permissions'])
            ->whereHas('role', function($query) {
                $query->where('name', 'admin');
            })
            ->orderBy('name')
            ->get();
            
        // Get all available permissions
        $permissions = Permission::orderBy('module')->orderBy('name')->get();
        
        // Group permissions by module for easier display
        $permissionsByModule = $permissions->groupBy('module');
        
        return view('super-admin.user-permissions.index', compact('users', 'permissions', 'permissionsByModule'));
    }

    /**
     * Show permission management for a specific user.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        // Load the user's departments and permissions
        $user->load(['departments.permissions', 'permissions']);
        
        // Get all available permissions
        $permissions = Permission::orderBy('module')->orderBy('name')->get();
        
        // Group permissions by module for easier display
        $permissionsByModule = $permissions->groupBy('module');
        
        // Get the user's department permissions
        $departmentPermissions = $user->departments->flatMap(function ($department) {
            return $department->permissions;
        })->pluck('id')->unique()->toArray();
        
        // Get the user's direct permissions
        $directPermissions = $user->permissions->pluck('id')->toArray();
        
        return view('super-admin.user-permissions.edit', compact('user', 'permissions', 'permissionsByModule', 'departmentPermissions', 'directPermissions'));
    }

    /**
     * Update the specified user's permissions.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return redirect()->route('super-admin.user-permissions.edit', $user)
                ->withErrors($validator)
                ->withInput();
        }

        // Sync the user's permissions
        $user->permissions()->sync($request->permissions ?? []);

        return redirect()->route('super-admin.user-permissions.edit', $user)
            ->with('success', 'User permissions updated successfully');
    }
} 