<?php

namespace App\Providers;

use App\Notifications\Channels\DatabaseChannel;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->extend(ChannelManager::class, function ($service, $app) {
            $service->extend('database', function () {
                return new DatabaseChannel();
            });
            
            return $service;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
} 