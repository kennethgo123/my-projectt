<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\DB;

class UserDepartmentObserver
{
    /**
     * Handle the Department "synced" event.
     * Set is_staff flag for users attached to departments
     */
    public function synced(Department $department, $relation)
    {
        if ($relation === 'users') {
            // Get all users who belong to this department and mark them as staff
            User::whereHas('departments', function($query) use ($department) {
                $query->where('departments.id', $department->id);
            })->update(['is_staff' => true]);
        }
    }

    /**
     * Handle the Department "attached" event.
     * Set is_staff flag for users attached to departments
     */
    public function attached(Department $department, $relation, $ids)
    {
        if ($relation === 'users') {
            // Update the is_staff flag for the attached users
            User::whereIn('id', (array) $ids)->update(['is_staff' => true]);
        }
    }

    /**
     * Handle the Department "detached" event.
     * Check if users should still be marked as staff after being detached from a department
     */
    public function detached(Department $department, $relation, $ids)
    {
        if ($relation === 'users') {
            // For each detached user, check if they still belong to any department
            foreach ((array) $ids as $userId) {
                $user = User::find($userId);
                if ($user && !$user->departments()->exists() && !$user->is_super_admin) {
                    // If user doesn't belong to any department and is not a super admin,
                    // remove the staff flag
                    $user->update(['is_staff' => false]);
                }
            }
        }
    }
}
