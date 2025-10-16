<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LawFirmMiddleware
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
        if (!$request->user() || !$request->user()->role || $request->user()->role->name !== 'law_firm') {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
} 