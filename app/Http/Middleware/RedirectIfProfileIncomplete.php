<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfProfileIncomplete
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // Check if user is a law firm and profile is not completed
            if ($user->role_id === 4 && !$user->lawFirmProfile) { // Assuming 4 is law firm role ID
                return redirect()->route('profile.complete');
            }
        }

        return $next($request);
    }
} 