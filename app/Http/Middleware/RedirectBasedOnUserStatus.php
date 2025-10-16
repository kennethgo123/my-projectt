<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectBasedOnUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // If email is verified but profile not completed, redirect to profile completion
            if ($user->hasVerifiedEmail() && !$user->profile_completed) {
                return redirect()->route('profile.complete');
            }
            
            // If profile is completed but account is pending approval
            if ($user->profile_completed && $user->status === 'pending') {
                return redirect()->route('profile.pending');
            }
            
            // If account is active, redirect to appropriate dashboard based on role
            if ($user->status === 'approved') {
                if ($request->is('/') || $request->is('home') || $request->is('dashboard')) {
                    switch ($user->role->name) {
                        case 'client':
                            return redirect()->route('client.welcome');
                        case 'lawyer':
                            return redirect()->route('lawyer.welcome');
                        case 'law_firm':
                            return redirect()->route('law-firm.dashboard');
                        case 'admin':
                            return redirect()->route('admin.dashboard');
                    }
                }
            }
        }
        
        return $next($request);
    }
} 