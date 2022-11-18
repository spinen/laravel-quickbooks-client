<?php

namespace Spinen\QuickBooks\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Spinen\QuickBooks\Client;

/**
 * Class ClientServiceProvider
 *
 * @package Spinen\QuickBooks
 */
class ClientServiceProvider extends LaravelServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [Client::class];
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Client::class, function (Application $app) {
            $token =
                $app->auth->user()->quickBooksToken ?:
                $app->auth
                    ->user()
                    ->quickBooksToken()
                    ->make();

            return new Client($app->config->get('quickbooks'), $token);
        });

        $this->app->alias(Client::class, 'QuickBooks');
    }
}
