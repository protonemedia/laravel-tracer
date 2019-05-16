# Very short description of the package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/protonemedia/laravel-tracer.svg?style=flat-square)](https://packagist.org/packages/protonemedia/laravel-tracer)
[![Build Status](https://img.shields.io/travis/protonemedia/laravel-tracer/master.svg?style=flat-square)](https://travis-ci.org/protonemedia/laravel-tracer)
[![Quality Score](https://img.shields.io/scrutinizer/g/protonemedia/laravel-tracer.svg?style=flat-square)](https://scrutinizer-ci.com/g/protonemedia/laravel-tracer)
[![Total Downloads](https://img.shields.io/packagist/dt/protonemedia/laravel-tracer.svg?style=flat-square)](https://packagist.org/packages/protonemedia/laravel-tracer)

This is where your description should go. Try and limit it to a paragraph or two, and maybe throw in a mention of what PSRs you support to avoid any confusion with users and contributors.

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
    Route::get('settings', 'SettingsController')->name('settings.show');
});
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