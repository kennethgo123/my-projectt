<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of department users.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::with('departments')->whereHas('departments')->get();
        $departments = Department::all();
        
        return view('super-admin.users.index', compact('users', 'departments'));
    }

    /**
     * Store a newly created department user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'departments' => 'required|array',
            'departments.*' => 'exists:departments,id',
        ]);

        if ($validator->fails()) {
            return redirect()->route('super-admin.users.index')
                ->withErrors($validator)
                ->withInput();
        }

        // Get admin role ID
        $adminRoleId = \App\Models\Role::where('name', 'admin')->first()->id;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $adminRoleId,
            'status' => 'approved',
            'profile_completed' => true,
        ]);

        // Attach departments
        $user->departments()->attach($request->departments);

        return redirect()->route('super-admin.users.index')
            ->with('success', 'User created successfully');
    }

    /**
     * Show the form for editing the specified user.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        $departments = Department::all();
        $user->load('departments');
        
        return view('super-admin.users.edit', compact('user', 'departments'));
    }

    /**
     * Update the specified user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:8',
            'departments' => 'required|array',
            'departments.*' => 'exists:departments,id',
        ]);

        if ($validator->fails()) {
            return redirect()->route('super-admin.users.edit', $user)
                ->withErrors($validator)
                ->withInput();
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        // Sync departments
        $user->departments()->sync($request->departments);

        return redirect()->route('super-admin.users.index')
            ->with('success', 'User updated successfully');
    }

    /**
     * Remove the specified user.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        // Don't allow super admin to be deleted
        if ($user->is_super_admin) {
            return redirect()->route('super-admin.users.index')
                ->with('error', 'Super admin cannot be deleted');
        }

        $user->departments()->detach();
        $user->delete();

        return redirect()->route('super-admin.users.index')
            ->with('success', 'User deleted successfully');
    }
}
