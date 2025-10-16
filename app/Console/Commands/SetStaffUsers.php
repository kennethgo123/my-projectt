<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SetStaffUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:set-staff-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set is_staff flag for admin users, department users, and super admins';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting staff flag for admin users, department users, and super admins...');
        
        // Get admin role ID
        $adminRole = Role::where('name', 'admin')->first();
        
        if (!$adminRole) {
            $this->error('Admin role not found!');
            return 1;
        }

        // Mark all users with admin role as staff
        $adminCount = User::where('role_id', $adminRole->id)
            ->update(['is_staff' => true]);
        
        $this->info("Marked {$adminCount} admin users as staff.");
        
        // Mark all department users as staff
        $deptUserCount = DB::table('users')
            ->join('department_user', 'users.id', '=', 'department_user.user_id')
            ->update(['users.is_staff' => true]);
            
        $this->info("Marked {$deptUserCount} department users as staff (if not already marked).");
        
        // Mark all super admins as staff
        $superAdminCount = User::where('is_super_admin', true)
            ->update(['is_staff' => true]);
            
        $this->info("Marked {$superAdminCount} super admin users as staff (if not already marked).");
        
        $this->info('Staff flag setting completed successfully.');
        
        return 0;
    }
}
