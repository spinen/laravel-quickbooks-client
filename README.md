# SPINEN's Laravel QuickBooks Client

[![Latest Stable Version](https://poser.pugx.org/spinen/laravel-quickbooks-client/v/stable)](https://packagist.org/packages/spinen/laravel-quickbooks-client)
[![Latest Unstable Version](https://poser.pugx.org/spinen/laravel-quickbooks-client/v/unstable)](https://packagist.org/packages/spinen/laravel-quickbooks-client)
[![Total Downloads](https://poser.pugx.org/spinen/laravel-quickbooks-client/downloads)](https://packagist.org/packages/spinen/laravel-quickbooks-client)
[![License](https://poser.pugx.org/spinen/laravel-quickbooks-client/license)](https://packagist.org/packages/spinen/laravel-quickbooks-client)

PHP client wrapping the [QuickBooks PHP SDK](https://github.com/intuit/QuickBooks-V3-PHP-SDK).

We solely use [Laravel](https://www.laravel.com) for our applications, so this package is written with Laravel in mind. If there is a request from the community to split this package into 2 parts to allow it to be used outside of Laravel, then we will consider doing that work.

## Build Status

| Branch | Status | Coverage | Code Quality |
| ------ | :----: | :------: | :----------: |
| Develop | [![Build Status](https://github.com/spinen/laravel-quickbooks-client/workflows/CI/badge.svg?branch=develop)](https://github.com/spinen/laravel-quickbooks-client/workflows/CI/badge.svg?branch=develop) | [![Code Coverage](https://scrutinizer-ci.com/g/spinen/laravel-quickbooks-client/badges/coverage.png?b=develop)](https://scrutinizer-ci.com/g/spinen/laravel-quickbooks-client/?branch=develop) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/spinen/laravel-quickbooks-client/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/spinen/laravel-quickbooks-client/?branch=develop) |
| Master | [![Build Status](https://github.com/spinen/laravel-quickbooks-client/workflows/CI/badge.svg?branch=master)](https://github.com/spinen/laravel-quickbooks-client/workflows/CI/badge.svg?branch=master) | [![Code Coverage](https://scrutinizer-ci.com/g/spinen/laravel-quickbooks-client/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/spinen/laravel-quickbooks-client/?branch=master) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/spinen/laravel-quickbooks-client/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/spinen/laravel-quickbooks-client/?branch=master) |

## Installation

1. Install QuickBooks PHP Client:

```bash
$ composer require spinen/laravel-quickbooks-client
```

2. Run our migration to install the `quickbooks_tokens` table:

```bash
$ php artisan migrate --package=spinen/laravel-quickbooks-client
```

The package uses the [auto registration feature](https://laravel.com/docs/packages#package-discovery) of Laravel.

## Configuration

1. You will need a ```quickBooksToken``` relationship on your ```User``` model.  There is a trait named ```Spinen\QuickBooks\HasQuickBooksToken```, which you can include on your ```User``` model, which will setup the relationship. To do this implement the following:

Add ```use Spinen\QuickBooks\HasQuickBooksToken;``` to your service container at the top of User.php
and also add the trait within the class. For example:

```php
class User extends Authenticatable
{
    use Notifiable, HasQuickBooksToken;
```

**NOTE: If your ```User``` model is not ```App\Models\User```, then you will need to configure the path in the ```configs/quickbooks.php```.**

2. Add the appropriate values to your ```.env```

    #### Minimal Keys
    ```bash
    QUICKBOOKS_CLIENT_ID=<client id given by QuickBooks>
    QUICKBOOKS_CLIENT_SECRET=<client secret>
    ```

    #### Optional Keys
    ```bash
    QUICKBOOKS_API_URL=<Development|Production> # Defaults to App's env value
    QUICKBOOKS_DEBUG=<true|false>               # Defaults to App's debug value
    ```

3. _[Optional]_ Publish configs & views

    #### Config
    A configuration file named ```quickbooks.php``` can be published to ```config/``` by running...

    ```bash
    php artisan vendor:publish --tag=quickbooks-config
    ```

    #### Views
    View files can be published by running...

    ```bash
    php artisan vendor:publish --tag=quickbooks-views
    ```

## Usage

Here is an example of getting the company information from QuickBooks:

### NOTE: Before doing these commands, go to your connect route (default: /quickbooks/connect) to get a QuickBooks token for your user

```php
php artisan tinker
Psy Shell v0.8.17 (PHP 7.1.14 — cli) by Justin Hileman
>>> Auth::logInUsingId(1)
=> App\Models\User {#1668
     id: 1,
     // Other keys removed for example
   }
>>> $quickbooks = app('Spinen\QuickBooks\Client') // or app('QuickBooks')
=> Spinen\QuickBooks\Client {#1613}
>>> $quickbooks->getDataService()->getCompanyInfo();
=> QuickBooksOnline\API\Data\IPPCompanyInfo {#1673
     +CompanyName: "Sandbox Company_US_1",
     +LegalName: "Sandbox Company_US_1",
     // Other properties removed for example
   }
>>>
```

You can call any of the resources as documented [in the SDK](https://intuit.github.io/QuickBooks-V3-PHP-SDK/quickstart.html).

## Middleware

If you have routes that will be dependent on the user's account having a usable QuickBooks OAuth token, there is an included middleware ```Spinen\QuickBooks\Laravel\Filter``` that gets registered as ```quickbooks``` that will ensure the account is linked and redirect them to the `connect` route if needed.

Here is an example route definition:

```php
Route::view('some/route/needing/quickbooks/token/before/using', 'some.view')
     ->middleware('quickbooks');
```
