<?php

use DimaBzz\LaravelConfigWriter\Exceptions\ConfigWriterException;

if (! function_exists('config_writer')) {
    /**
     * Write new values to the configuration file.
     *
     * @param  dynamic  array|configName,array
     * @return bool
     *
     * @throws Exception
     */
    function config_writer(): bool
    {
        $arguments = func_get_args();

        if (empty($arguments)) {
            return false;
        }

        if (is_array($arguments[0])) {
            return app('configwriter')->write($arguments[0]);
        }

        if (is_string($arguments[0])) {
            if (! isset($arguments[1]) || ! is_array($arguments[1])) {
                throw ConfigWriterException::requiredParamArray();
            }

            return app('configwriter')->of($arguments[1])->configFile($arguments[0])->write();
        }

        return false;
    }
}
