<?php

namespace Spinen\QuickBooks\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

/**
 * Class ServiceProvider
 *
 * @package Spinen\QuickBooks
 */
class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerMiddleware();

        $this->registerPublishes();

        $this->registerRoutes();

        $this->registerViews();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/quickbooks.php', 'quickbooks');
    }

    /**
     * Register the middleware
     *
     * If a route needs to have the QuickBooks client, then make sure that the user has linked their account.
     *
     */
    public function registerMiddleware()
    {
        $this->app->router->aliasMiddleware('quickbooks', Filter::class);
    }

    /**
     * There are several resources that get published
     *
     * Only worry about telling the application about them if running in the console.
     *
     */
    protected function registerPublishes()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

            $this->publishes([
                __DIR__ . '/../config/quickbooks.php' => config_path('quickbooks.php'),
            ], 'quickbooks-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'quickbooks-migrations');

            $this->publishes([
                __DIR__ . '/../resources/views' => base_path('resources/views/vendor/quickbooks'),
            ], 'quickbooks-views');
        }
    }

    /**
     * Register the routes needed for the registration flow
     */
    protected function registerRoutes()
    {
        $config = $this->app->config->get('quickbooks.route');

        $this->app->router->prefix($config['prefix'])
                          ->as('quickbooks.')
                          ->middleware($config['middleware']['default'])
                          ->namespace('Spinen\QuickBooks')
                          ->group(function (Router $router) use ($config) {
                              $router->get($config['paths']['connect'], 'Controller@connect')
                                     ->middleware($config['middleware']['authenticated'])
                                     ->name('connect');

                              $router->delete($config['paths']['disconnect'], 'Controller@disconnect')
                                     ->middleware($config['middleware']['authenticated'])
                                     ->name('disconnect');

                              $router->get($config['paths']['token'], 'Controller@token')
                                     ->name('token');
                          });
    }

    /**
     * Register the views
     */
    protected function registerViews()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'quickbooks');
    }
}
