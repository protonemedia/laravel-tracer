<?php

namespace Protonemedia\LaravelTracer;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Protonemedia\LaravelTracer\QualifiedRoute;

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
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'laravel-tracer');

        // setter for the qualified route
        Request::macro('qualifyRoute', function (string $name, $secondsBetweenLogs = null) {
            $this->qualifiedRoute = new QualifiedRoute($this->route(), $name, $secondsBetweenLogs);
        });

        // getter for the qualified route
        Request::macro('qualifiedRoute', function (): QualifiedRoute {
            return $this->qualifiedRoute ?: QualifiedRoute::fromRequest($this);
        });
    }
}
