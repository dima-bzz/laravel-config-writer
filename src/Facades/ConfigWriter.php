<?php

namespace DimaBzz\LaravelConfigWriter\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool write(array $newValues)
 * @method static \DimaBzz\LaravelConfigWriter\ConfigWriter setConfig(string $name)
 *
 * @see \DimaBzz\LaravelConfigWriter\ConfigWriter
 */
class ConfigWriter extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'configwriter';
    }
}
