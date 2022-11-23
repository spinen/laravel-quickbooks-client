<?php

use App\Models\User;

return [

    /*
    |--------------------------------------------------------------------------
    | Properties for the QuickBooks SDK DataService
    |--------------------------------------------------------------------------
    |
    | The configuration keys for the SDK are inconsistent in naming convention.
    | We are adhering to snake_case.  We make a sensible guess for 'base_url'
    | using the app's env, but you can can it with 'QUICKBOOKS_API_URL'.  Also,
    | the 'redirect_uri' is made in the client from the 'quickbooks.token'
    | named route, so it cannot be configured here.
    |
    | Most of the time, only 'QUICKBOOKS_CLIENT_ID' & 'QUICKBOOKS_CLIENT_SECRET'
    | needs to be set.
    |
    | See: https://intuit.github.io/QuickBooks-V3-PHP-SDK/configuration.html
    |
    */

    'data_service' => [
        'auth_mode' => 'oauth2',
        'base_url' => env('QUICKBOOKS_API_URL', config('app.env') === 'production' ? 'Production' : 'Development'),
        'client_id' => env('QUICKBOOKS_CLIENT_ID'),
        'client_secret' => env('QUICKBOOKS_CLIENT_SECRET'),
        'scope' => 'com.intuit.quickbooks.accounting',
    ],

    /*
    |--------------------------------------------------------------------------
    | Properties to control logging
    |--------------------------------------------------------------------------
    |
    | Configures logging to <storage_path>/logs/quickbooks.log when in debug
    | mode or when 'QUICKBOOKS_DEBUG' is true.
    |
    */

    'logging' => [
        'enabled' => env('QUICKBOOKS_DEBUG', config('app.debug')),

        'location' => storage_path('logs'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Properties to configure the routes
    |--------------------------------------------------------------------------
    |
    | There are several routes that are needed for the package, so these
    | properties allow configuring them to fit the application as needed.
    |
    */

    'route' => [
        // Controls the middlewares for thr routes.  Can be a string or array of strings
        'middleware' => [
            // Added to the protected routes for the package (i.e. connect & disconnect)
            'authenticated' => 'auth',
            // Added to all of the routes for the package
            'default' => 'web',
        ],
        'paths' => [
            // Show forms to connect/disconnect
            'connect' => 'connect',
            // The DELETE takes place to remove token
            'disconnect' => 'disconnect',
            // Return URI that QuickBooks sends code to allow getting OAuth token
            'token' => 'token',
        ],
        'prefix' => 'quickbooks',
    ],

    /*
    |--------------------------------------------------------------------------
    | Properties for control the "user" relationship in Token
    |--------------------------------------------------------------------------
    |
    | The Token class has a "user" relationship, and these properties allow
    | configuring the relationship.
    |
    */

    'user' => [
        'keys' => [
            'foreign' => 'user_id',
            'owner' => 'id',
        ],
        'model' => User::class,
    ],

];
