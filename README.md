# Laravel Tracer

[![Latest Version on Packagist](https://img.shields.io/packagist/v/protonemedia/laravel-tracer.svg?style=flat-square)](https://packagist.org/packages/protonemedia/laravel-tracer)
[![Build Status](https://img.shields.io/travis/pascalbaljetmedia/laravel-tracer/master.svg?style=flat-square)](https://travis-ci.org/pascalbaljetmedia/laravel-tracer)
[![Quality Score](https://img.shields.io/scrutinizer/g/pascalbaljetmedia/laravel-tracer.svg?style=flat-square)](https://scrutinizer-ci.com/g/pascalbaljetmedia/laravel-tracer)
[![Total Downloads](https://img.shields.io/packagist/dt/protonemedia/laravel-tracer.svg?style=flat-square)](https://packagist.org/packages/protonemedia/laravel-tracer)



## Installation

You can install the package via composer:

```bash
composer require protonemedia/laravel-tracer
```

## Usage

Add the `QualifyRoute` middleware to your Route Middleware stack:

``` php
class Kernel extends \Illuminate\Foundation\Http\Kernel
{
    protected $routeMiddleware = [
        // ...
        'qualify' => \Protonemedia\LaravelTracer\Middleware\QualifyRoute::class,
        // ...
    ];
}
```

Add the `TraceUser` middleware to the routes or groups you want to trace, for example in the `web.php` routes file:

```php
use Protonemedia\LaravelTracer\Middleware\TraceUser;

Route::group(['middleware' => [TraceUser::class]], function () {
    Route::get('home', 'HomeController');

    Route::get('settings', 'SettingsController')->name('settings.show');

    Route::get('privacy-policy', 'PrivacyPolicyController')->name('privacyPolicy.show')->middleware('qualify:terms');

    Route::get('profile/notifications', 'NotificationsController')->name('notifications.index')->middleware('qualify:notifications,60');

    Route::group(['middleware' => ['qualify:finance']], function () {
        Route::get('invoices', 'InvoicesController@index')->name('invoices.index');
        Route::get('invoices/{id}', 'InvoicesController@show')->name('invoices.show');
    });

    Route::get('machine/{id}', 'MachineController');
    Route::get('rack/{id}', 'RackController')->middleware('qualify:rack');
    Route::get('server/{id}', 'ServerController')->middleware('qualify:server.{id}');
});
```

### Qualify routes

As you can see there are some example routes added to the group of routes we want to trace. Let's explain how each route will be qualified.

* A GET request to `/home` will simply be qualified as `home` since it has no name and no qualifier.
* A GET request to `/settings` will be qualified as `settings.show` because it has a name but still no qualifier.
* A GET request to `/privacy-policy` will be qualified as `terms`, this has been accomplished with the `qualify` middleware.

### Rate limiting

* A GET request to `/profile/notifications` will be qualified as `notifications` and as you can see, the `qualify` middleware was given a second parameter. This is the number of seconds between each log of this qualifier. When a user visits this route more than once in 60 seconds, it will be stored as 1 request.

### Grouped qualifiers

* A GET request to both `/invoices` and `/invoices/1` will be qualified as `finance`.

### Parameters

* A GET request `/machine/1` will be qualified as `machine/1`, it has no name and no qualifier so the `path` will be used as qualifier.
* A GET request to both `/rack/1` and `/rack/2` will be qualified as `rack`. Though the route has a parameter, the qualifier will be used.
* A GET request to `/server/1` will be qualified as `server.1`. The parameter in the qualifier will be replaced with the actual value used in the path.

### Qualify from the controller

This package adds two macros to `Illuminate\Http\Request`. The first one is `qualifyAs`. Just as the `QualifyRoute` middleware this method takes two parameters. The first parameter is the name and the second paramater (optional) is the number seconds to be used by the Rate Limiter. From your controller you could qualify the route using the `Request` object or by using the `request()` helper method. The other macro is `qualifiedRoute` which is a getter for the `QualifiedRoute` instance.

```php
use Illuminate\Http\Request;

class TicketsController extends Controller
{
    public function index()
    {
        request()->qualifyAs('tickets', 60);
    }

    public function show(Request $request, $id)
    {
        $request->qualifyAs('tickets');
    }

    public function delete(Request $request, $id)
    {
        $qualifiedRoute = $request->qualifiedRoute();

        $qualifiedRoute->name();
        $qualifiedRoute->secondsBetweenLogs();
    }
}
```

### Config

The config file consists of only two options. The first option is `seconds_between_logs` which can be used to set a default for the Rate Limiter. The second option is `should_trace_user` where you can specify a class@method which should return a boolean that specifies wether to trace or not. It takes two parameters: $request and $response:

```php

// config/laravel-tracer.php:
return [
    'seconds_between_logs' => 120,

    'should_trace_user' => 'MyClass@shouldTrace',
];

// MyClass.php

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MyClass
{
    public function shouldTrace(Request $request, Response $response): bool
    {
        return !$request->user()->isAdmin();
    }
}
```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email pascal@protone.media instead of using the issue tracker.

## Credits

- [Pascal Baljet](https://github.com/protonemedia)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
