<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\MaintenanceSchedule;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceModeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if there's an active maintenance schedule
        if (MaintenanceSchedule::hasActiveMaintenance()) {
            // Allow access for authenticated staff users (admin, super admin, or department users)
            if (auth()->check()) {
                $user = auth()->user();
                
                // Allow super admins, regular admins, or department users
                if ($user->isSuperAdmin() || 
                    $user->isAdmin() || 
                    $user->departments()->exists()) {
                    return $next($request);
                }
            }
            
            // Block all other users (clients, lawyers, law firms, or unauthenticated users)
            // If it's an AJAX request, return JSON response
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'LexCav is currently undergoing system maintenance so we can serve you better. Thank you for your patience.',
                    'maintenance' => true
                ], 503);
            }
            
            // For regular requests, show maintenance page
            return response()->view('maintenance', [
                'maintenance' => MaintenanceSchedule::getCurrentActiveMaintenance()
            ], 503);
        }

        return $next($request);
    }
}
