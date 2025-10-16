<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        // If user belongs to a department, ensure they only access authorized sections
        if (auth()->user()->departments()->exists() && !auth()->user()->isSuperAdmin()) {
            // Determine which section the user is trying to access
            $path = $request->path();
            
            // User Management permissions check
            if (str_contains($path, 'admin/users') || str_contains($path, 'admin/pending-users') || str_contains($path, 'admin/deactivated-users')) {
                if (!auth()->user()->hasPermission('view_user_list') && 
                    !auth()->user()->hasPermission('approve_users') && 
                    !auth()->user()->hasPermission('deactivate_users')) {
                    abort(403, 'You do not have permission to access user management.');
                }
            }
            
            // Financial permissions check
            else if (str_contains($path, 'admin/sales-panel') || str_contains($path, 'admin/subscriptions')) {
                if (!auth()->user()->hasPermission('view_sales_panel') && 
                    !auth()->user()->hasPermission('manage_subscriptions')) {
                    abort(403, 'You do not have permission to access financial management.');
                }
            }
            
            // Law Services permissions check
            else if (str_contains($path, 'admin/services')) {
                if (!auth()->user()->hasPermission('manage_law_services') && 
                    !auth()->user()->hasPermission('delete_law_services')) {
                    abort(403, 'You do not have permission to access law services management.');
                }
            }
            
            // Maintenance Management permissions check
            else if (str_contains($path, 'admin/maintenance')) {
                if (!auth()->user()->hasPermission('schedule_maintenance') && 
                    !auth()->user()->hasPermission('enable_maintenance_mode') &&
                    !auth()->user()->hasPermission('view_maintenance_logs')) {
                    abort(403, 'You do not have permission to access maintenance management.');
                }
            }
            
            // Dashboard - redirect to the appropriate section they have permission for
            else if (str_contains($path, 'admin/dashboard') || $path === 'admin') {
                // Allow dashboard access for all department users
                // The dashboard will show only the sections they have permission for
            }
            
            // If trying to access any other admin section without permission
            else if (str_starts_with($path, 'admin/') && !$this->hasAnyPermission()) {
                abort(403, 'You do not have permission to access this section.');
            }
        }

        return $next($request);
    }
    
    /**
     * Check if user has any admin permissions
     */
    private function hasAnyPermission()
    {
        $permissions = [
            'view_user_list', 'approve_users', 'deactivate_users',
            'view_sales_panel', 'manage_subscriptions',
            'manage_law_services', 'delete_law_services',
            'schedule_maintenance', 'enable_maintenance_mode', 'view_maintenance_logs'
        ];
        
        foreach ($permissions as $permission) {
            if (auth()->user()->hasPermission($permission)) {
                return true;
            }
        }
        
        return false;
    }
} 