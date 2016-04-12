<?php

namespace OpiloClient\Laravel;

use Illuminate\Support\ServiceProvider;

class OpiloServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/opilo.php' => config_path('opilo.php'),
        ]);
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->singleton(HttpClient::class, function ($app) {
            return new HttpClient(config('opilo.username'), config('opilo.password'), config('opilo.url'));
        });
    }
}
