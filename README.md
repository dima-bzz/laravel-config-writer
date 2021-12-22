# Laravel Config Writer

[![Latest Stable Version](https://img.shields.io/packagist/v/dima-bzz/laravel-config-writer)](https://packagist.org/packages/dima-bzz/laravel-config-writer)
![Tests](https://github.com/dima-bzz/laravel-config-writer/workflows/Tests/badge.svg)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/dima-bzz/laravel-config-writer/Check%20&%20fix%20styling?label=code%20style)](https://github.com/dima-bzz/laravel-config-writer/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/dima-bzz/laravel-config-writer)](https://packagist.org/packages/dima-bzz/laravel-config-writer)

Write to Laravel Config files and maintain file integrity.

This library adds the ability to write to configuration files.

You can rewrite array values inside a basic configuration file that returns a single array definition (like a Laravel config file) whilst maintaining the file integrity, leaving comments and advanced settings intact.

The following value types are supported for writing: strings, integers, booleans and single-dimension arrays.

## Support

This provider is designed to be used in Laravel from `7.0 and 8.0` version.

## Setup

Install through composer:

```
composer require "dima-bzz/laravel-config-writer"
```

Set a filename to use as default in the `.env` file:

```
...
CONFIG_WRITER=config
...
```

You can optionally publish the config file with:
```bash
php artisan vendor:publish --tag="config-writer"
```

After you've configured everything you should run the command `artisan config:clear` or `artisan config:cache`.

## Introduction

The default is strict write mode. If you wish, you can change it in the configuration file:

```php
...
strict => false
...
```

Or through the Facade:

```php
use DimaBzz\LaravelConfigWriter\Facades\ConfigWriter
...
ConfigWriter::of([
    'item' => 'new value',
    'nested.config.item' => 'value',
    'arrayItem' => ['Single', 'Level', 'Array', 'Values'],
    'numberItem' => 3,
    'booleanItem' => true
])
->strictMode(false)
->write();
...
```


## Usage the helper

This is the easiest way to write new data to the config file:

```php
config_writer([
    'item' => 'new value',
    'nested.config.item' => 'value',
    'arrayItem' => ['Single', 'Level', 'Array', 'Values'],
    'numberItem' => 3,
    'booleanItem' => true
]);
```

Set another config file optional:

```php
config_writer('config-writer', [
    'item' => 'new value',
    'nested.config.item' => 'value',
    'arrayItem' => ['Single', 'Level', 'Array', 'Values'],
    'numberItem' => 3,
    'booleanItem' => true
]);
```

## Usage the Facade

You can write new data to the config file like this:

```php
...
ConfigWriter::write([
    'item' => 'new value',
    'nested.config.item' => 'value',
    'arrayItem' => ['Single', 'Level', 'Array', 'Values'],
    'numberItem' => 3,
    'booleanItem' => true
]);
...
```

Also, you can set certain parameters:

```php
...
ConfigWriter::of([
    'item' => 'new value',
    'nested.config.item' => 'value',
    'arrayItem' => ['Single', 'Level', 'Array', 'Values'],
    'numberItem' => 3,
    'booleanItem' => true
])
->config('config-writer')
->strictMode(false)
->write();
...
```

## Events

#### `DimaBzz\LaravelConfigWriter\Events\WriteSuccess`

This event will be fired if writing to the configuration file was successful. It has the following public properties:

- `name`: configuration file name

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security

If you discover any security related issues, please email [dimabzz@gmail.com](mailto:dimabzz@gmail.com) instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.