<?php

if (! function_exists('config_writer')) {
    /**
     * Write new values to the configuration file.
     *
     * @param  dynamic  array|configName,array
     * @return bool
     *
     * @throws \Exception
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
                throw new Exception(
                    'When setting a new value in the config file, you must pass an array of key / value pairs.'
                );
            }

            return app('configwriter')->setConfig($arguments[0])->write($arguments[1]);
        }

        return false;
    }
}
