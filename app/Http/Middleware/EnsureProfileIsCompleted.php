<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileIsCompleted
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // If profile is not completed, redirect to profile completion
            if (!$user->profile_completed && !$request->routeIs('profile.complete')) {
                return redirect()->route('profile.complete');
            }
            
            // If profile is completed but not approved, show pending approval page
            if ($user->profile_completed && $user->status === 'pending' && 
                !$request->routeIs('profile.pending') && 
                !$user->isAdmin()) {
                return redirect()->route('profile.pending');
            }
        }

        return $next($request);
    }
} 