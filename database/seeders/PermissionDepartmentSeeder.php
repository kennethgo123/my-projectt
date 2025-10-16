<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Department;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PermissionDepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin role ID
        $adminRole = Role::where('name', 'admin')->first();
        
        if (!$adminRole) {
            $this->command->error('Admin role not found. Please run migrations and basic seeders first.');
            return;
        }
        
        // 1. Create Permissions
        
        // User Management permissions
        $userManagementPermissions = [
            [
                'name' => 'View User List',
                'slug' => 'view_user_list',
                'description' => 'Can view the list of all users',
                'module' => 'user_management'
            ],
            [
                'name' => 'Approve Users',
                'slug' => 'approve_users',
                'description' => 'Can approve pending user registrations',
                'module' => 'user_management'
            ],
            [
                'name' => 'Deactivate Users',
                'slug' => 'deactivate_users',
                'description' => 'Can deactivate or suspend user accounts',
                'module' => 'user_management'
            ],
        ];
        
        // Financial permissions
        $financialPermissions = [
            [
                'name' => 'View Sales Panel',
                'slug' => 'view_sales_panel',
                'description' => 'Can access and view the sales dashboard',
                'module' => 'financial'
            ],
            [
                'name' => 'Manage Subscriptions',
                'slug' => 'manage_subscriptions',
                'description' => 'Can manage user subscriptions',
                'module' => 'financial'
            ],
        ];
        
        // Law Services permissions
        $lawServicesPermissions = [
            [
                'name' => 'Manage Law Services',
                'slug' => 'manage_law_services',
                'description' => 'Can create and edit legal service offerings',
                'module' => 'services'
            ],
            [
                'name' => 'Delete Law Services',
                'slug' => 'delete_law_services',
                'description' => 'Can delete legal service offerings',
                'module' => 'services'
            ],
        ];
        
        // Create all permissions
        $allPermissions = array_merge($userManagementPermissions, $financialPermissions, $lawServicesPermissions);
        
        foreach ($allPermissions as $permissionData) {
            Permission::create($permissionData);
        }
        
        // 2. Create Departments
        $departments = [
            [
                'name' => 'User Management Department',
                'description' => 'Responsible for user approvals, monitoring, and management',
                'permissions' => array_column($userManagementPermissions, 'slug'),
            ],
            [
                'name' => 'Financial Department',
                'description' => 'Handles financial operations, sales tracking, and subscriptions',
                'permissions' => array_column($financialPermissions, 'slug'),
            ],
            [
                'name' => 'Law Services Department',
                'description' => 'Manages the legal service offerings of the platform',
                'permissions' => array_column($lawServicesPermissions, 'slug'),
            ]
        ];
        
        foreach ($departments as $deptData) {
            $permissions = $deptData['permissions'];
            unset($deptData['permissions']);
            
            $department = Department::create($deptData);
            
            // Attach permissions to department
            $permissionIds = Permission::whereIn('slug', $permissions)->pluck('id');
            $department->permissions()->attach($permissionIds);
        }
        
        // 3. Create Users
        $users = [
            [
                'name' => 'User Department 1',
                'email' => 'userdept1@gmail.com',
                'department' => 'User Management Department',
            ],
            [
                'name' => 'User Department 2',
                'email' => 'userdept2@gmail.com',
                'department' => 'User Management Department',
            ],
            [
                'name' => 'Finance Department 1',
                'email' => 'financedept1@gmail.com',
                'department' => 'Financial Department',
            ],
            [
                'name' => 'Finance Department 2',
                'email' => 'financedept2@gmail.com',
                'department' => 'Financial Department',
            ],
            [
                'name' => 'Law Department 1',
                'email' => 'lawdept1@gmail.com',
                'department' => 'Law Services Department',
            ],
            [
                'name' => 'Law Department 2',
                'email' => 'lawdept2@gmail.com',
                'department' => 'Law Services Department',
            ],
        ];
        
        foreach ($users as $userData) {
            $departmentName = $userData['department'];
            unset($userData['department']);
            
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
                'role_id' => $adminRole->id,
                'status' => 'approved',
                'profile_completed' => true,
                'remember_token' => Str::random(10),
            ]);
            
            // Attach department to user
            $department = Department::where('name', $departmentName)->first();
            $user->departments()->attach($department);
        }
        
        $this->command->info('Permissions, departments, and users created successfully!');
    }
}
