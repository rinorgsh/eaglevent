<?php

namespace App\Providers;

use App\Services\PretixService;
use Illuminate\Support\ServiceProvider;

class PretixServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(PretixService::class, function ($app) {
            return new PretixService();
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