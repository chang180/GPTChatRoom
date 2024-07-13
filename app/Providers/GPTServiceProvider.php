<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\GPTService;

class GPTServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(GPTService::class, function ($app) {
            return new GPTService();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
