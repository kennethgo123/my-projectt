<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DepartmentPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission = null): Response
    {
        if (!auth()->check()) {
            abort(403, 'Unauthorized access. Please login.');
        }
        
        // Super admins have all permissions
        if (auth()->user()->isSuperAdmin()) {
            return $next($request);
        }
        
        // If permission is required and user doesn't have it, abort
        if ($permission && !auth()->user()->hasPermission($permission)) {
            abort(403, 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
