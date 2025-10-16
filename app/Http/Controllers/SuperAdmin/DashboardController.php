<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\User;
use App\Models\Permission;

class DashboardController extends Controller
{
    /**
     * Show the super admin dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $departments = Department::count();
        $usersInDepartments = User::whereHas('departments')->count();
        $permissions = Permission::count();
        
        return view('super-admin.dashboard', compact('departments', 'usersInDepartments', 'permissions'));
    }
}
