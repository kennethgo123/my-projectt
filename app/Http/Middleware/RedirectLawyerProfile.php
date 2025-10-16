<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectLawyerProfile
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->isLawyer() && $request->routeIs('profile.show')) {
            return redirect()->route('lawyers.optimize-profile');
        }

        return $next($request);
    }
} 