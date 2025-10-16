<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class AuthenticatedSessionController extends Controller
{
    public function store(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        // Check if user exists
        if (!$user) {
            return back()->withErrors([
                'email' => 'No account found with this email address.',
            ])->withInput();
        }

        // Check if email is verified
        if ($user && !$user->hasVerifiedEmail()) {
            return back()->withErrors([
                'email' => 'Please verify your email address before logging in. Check your inbox for the verification link.',
            ])->withInput();
        }

        // Check if user is deactivated
        if ($user && !$user->is_active) {
            return back()->withErrors([
                'email' => 'Your account has been deactivated. Please contact the administrator.',
            ])->withInput();
        }

        // Attempt authentication
        try {
            $request->authenticate();
        } catch (\Exception $e) {
            return back()->withErrors([
                'email' => 'The provided credentials are incorrect.',
            ])->withInput();
        }

        $request->session()->regenerate();

        if (!$user->profile_completed) {
            return redirect()->route('profile.complete');
        }

        return redirect()->intended($this->getDashboardRoute($user));
    }

    protected function getDashboardRoute($user)
    {
        switch ($user->role->name) {
            case 'client':
                return route('client.welcome');
            case 'lawyer':
                return route('lawyer.welcome');
            case 'law_firm':
                return route('law-firm.dashboard');
            default:
                return RouteServiceProvider::HOME;
        }
    }
} 