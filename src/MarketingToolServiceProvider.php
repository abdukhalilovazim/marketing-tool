<?php

namespace Revo\MarketingTool;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class MarketingToolServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Load package routes
        $this->registerRoutes();

        // Load package views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'marketing-tool');

        // Publish package configuration
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/marketing-tool.php' => config_path('marketing-tool.php'),
            ], 'marketing-tool-config');
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Merge package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/marketing-tool.php', 'marketing-tool');
    }

    /**
     * Register package routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        Route::middleware(config('marketing-tool.middleware'))
            ->prefix(config('marketing-tool.prefix'))
            ->group(__DIR__ . '/../routes/web.php');
    }
}
