<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Department;

class ITOperationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create IT Operations permissions
        $itOperationsPermissions = [
            [
                'name' => 'Enable Maintenance Mode',
                'slug' => 'enable_maintenance_mode',
                'description' => 'Can enable and disable system maintenance mode',
                'module' => 'it_operations'
            ],
            [
                'name' => 'Schedule Maintenance',
                'slug' => 'schedule_maintenance',
                'description' => 'Can schedule maintenance windows with specific start/end times',
                'module' => 'it_operations'
            ],
            [
                'name' => 'View Maintenance Logs',
                'slug' => 'view_maintenance_logs',
                'description' => 'Can view maintenance activity logs and history',
                'module' => 'it_operations'
            ],
        ];

        // Create all permissions
        foreach ($itOperationsPermissions as $permissionData) {
            Permission::firstOrCreate(
                ['slug' => $permissionData['slug']], // Check by slug to avoid duplicates
                $permissionData
            );
        }

        // 2. Create IT Operations/Infrastructure Department
        $department = Department::firstOrCreate(
            ['name' => 'IT Operations/Infrastructure'],
            [
                'name' => 'IT Operations/Infrastructure',
                'description' => 'Manages system maintenance, infrastructure operations, and technical system administration',
            ]
        );

        // Attach permissions to department
        $permissionIds = Permission::whereIn('slug', array_column($itOperationsPermissions, 'slug'))->pluck('id');
        $department->permissions()->sync($permissionIds);

        $this->command->info('IT Operations/Infrastructure department and permissions created successfully!');
    }
}
