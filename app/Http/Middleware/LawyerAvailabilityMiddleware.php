<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LawyerAvailabilityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Check if user is a lawyer
        if ($user && $user->role->name === 'lawyer') {
            // Check if lawyer belongs to a firm
            if ($user->firm_id) {
                // Get the firm and check setting
                $lawFirm = \App\Models\User::find($user->firm_id);
                if ($lawFirm && $lawFirm->lawFirmProfile && !$lawFirm->lawFirmProfile->allow_lawyer_availability) {
                    // Redirect to consultations page if not allowed
                    return redirect()->route('lawyer.consultations')
                        ->with('error', 'Access to manage availability has been restricted by your firm. Kindly refer to your firm for details.');
                }
            }
        }

        return $next($request);
    }
}
