<?php

namespace App\Providers;

use App\Models\Department;
use App\Observers\UserDepartmentObserver;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set default timezone to Philippine Standard Time
        date_default_timezone_set('Asia/Manila');
        
        // Register our custom components
        Blade::component('client-nav-dropdown', \App\View\Components\ClientNavDropdown::class);
        
        // Register mail views - keep this simple to avoid conflicts
        View::addNamespace('mail', resource_path('views/vendor/mail'));
        
        // Register observers
        Department::observe(UserDepartmentObserver::class);
    }
}
