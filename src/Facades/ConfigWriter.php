<?php

namespace DimaBzz\LaravelConfigWriter\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool write(array|null $newValues)
 * @method static \DimaBzz\LaravelConfigWriter\ConfigWriter of(array $newValues)
 * @method static \DimaBzz\LaravelConfigWriter\ConfigWriter configFile(string $name)
 * @method static \DimaBzz\LaravelConfigWriter\ConfigWriter strictMode(bool $strictMode)
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
