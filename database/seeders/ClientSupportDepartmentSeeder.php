<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Department;

class ClientSupportDepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Client Support permissions
        $clientSupportPermissions = [
            [
                'name' => 'View Client Reports',
                'slug' => 'view_client_reports',
                'description' => 'Can view client reports filed against lawyers and law firms',
                'module' => 'client_support'
            ],
            [
                'name' => 'Review Client Reports',
                'slug' => 'review_client_reports',
                'description' => 'Can review and change the status of client reports',
                'module' => 'client_support'
            ],
            [
                'name' => 'Resolve Client Reports',
                'slug' => 'resolve_client_reports',
                'description' => 'Can mark client reports as resolved or dismissed',
                'module' => 'client_support'
            ],
            [
                'name' => 'Add Report Notes',
                'slug' => 'add_report_notes',
                'description' => 'Can add administrative notes to client reports',
                'module' => 'client_support'
            ],
            [
                'name' => 'View Report Documents',
                'slug' => 'view_report_documents',
                'description' => 'Can view and download supporting documents from client reports',
                'module' => 'client_support'
            ],
            [
                'name' => 'Contact Report Parties',
                'slug' => 'contact_report_parties',
                'description' => 'Can contact clients and lawyers/law firms involved in reports',
                'module' => 'client_support'
            ],
        ];

        // Create all permissions
        foreach ($clientSupportPermissions as $permissionData) {
            Permission::create($permissionData);
        }

        // 2. Create Client Support Services Department
        $department = Department::create([
            'name' => 'Client Support Services',
            'description' => 'Handles client reports filed against lawyers and law firms, provides support and ensures quality service standards',
        ]);

        // Attach permissions to department
        $permissionIds = Permission::whereIn('slug', array_column($clientSupportPermissions, 'slug'))->pluck('id');
        $department->permissions()->attach($permissionIds);

        $this->command->info('Client Support Services department and permissions created successfully!');
    }
}
