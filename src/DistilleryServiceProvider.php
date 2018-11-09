<?php

namespace matejsvajger\Distillery;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class DistilleryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerRoutes();
        $this->registerPublishing();

        $this->loadViewsFrom(
            __DIR__.'/../resources/views',
            'distillery'
        );
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    private function registerRoutes()
    {
        if (config('distillery.routing.enabled')) {
            Route::middlewareGroup('distillery', config('distillery.routing.middleware', []));

            Route::group($this->routeConfiguration(), function () {
                $this->loadRoutesFrom(__DIR__.'/Http/routes.php');
            });
        }
    }

    /**
     * Get the Telescope route group configuration array.
     *
     * @return array
     */
    private function routeConfiguration()
    {
        return [
            'namespace' => 'matejsvajger\Distillery\Http\Controllers',
            'prefix' => config('distillery.routing.path'),
            'middleware' => 'distillery',
        ];
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    private function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__. '/../config/distillery.php' => config_path('distillery.php'),
            ], 'distillery-config');
        }
    }
    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__. '/../config/distillery.php',
            'distillery'
        );

        $this->app->singleton(Distillery::class, function ($app) {
            return new Distillery($app->get('request'));
        });

        $this->app->alias(Distillery::class, 'distillery');

        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\DistilleryFilterCommand::class,
            ]);
        }
    }
}
