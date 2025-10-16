<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                $redirectRoute = match ($user->role->name) {
                    'admin' => '/admin/dashboard', // Consider using route('admin.dashboard') if defined
                    'client' => route('client.welcome'),
                    'lawyer' => route('lawyer.welcome'),
                    'law_firm' => RouteServiceProvider::LAW_FIRM_DASHBOARD,
                    default => RouteServiceProvider::HOME,
                };
                return redirect($redirectRoute);
            }
        }

        return $next($request);
    }
} 