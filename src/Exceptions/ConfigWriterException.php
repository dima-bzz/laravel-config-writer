<?php
namespace DimaBzz\LaravelConfigWriter\Exceptions;

use Exception;

class ConfigWriterException extends Exception
{
    public static function requiredParamArray()
    {
        return new static(
            'When setting a new value in the config file, you must pass an array of key / value pairs.'
        );
    }

    public static function requiredDefaultFileName()
    {
        return new static(
            'Default config file name not set.'
        );
    }

    public static function fileNotWritable(string $file)
    {
        return new static(
            sprintf('The config file %s does not support writing.', $file)
        );
    }

    public static function fileNotFound(string $file)
    {
        return new static(
            sprintf('Configuration file %s not found.', $file)
        );
    }

    public static function writeFailed(string $key)
    {
        return new static(
            sprintf('Unable to rewrite key %s in config, write failed.', $key)
        );
    }

    public static function keyDoesNotExist(string $key)
    {
        return new static(
            sprintf('Unable to rewrite key %s in config, does it exist?', $key)
        );
    }
}