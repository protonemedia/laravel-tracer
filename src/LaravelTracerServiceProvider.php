<?php

namespace Protonemedia\LaravelTracer;

use Illuminate\Support\ServiceProvider;

class LaravelTracerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('laravel-tracer.php'),
            ], 'config');

            if (!class_exists('CreateUserRequestsTable')) {
                $this->publishes([
                    __DIR__ . '/../database/migrations/create_user_requests_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_user_requests_table.php'),
                ], 'migrations');
            }
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'laravel-tracer');
    }
}
