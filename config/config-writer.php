<?php

/**
 * Copyright (c) Dmitry Mazurov.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://github.com/dima-bzz/laravel-config-writer
 */

return [
    /*
     |--------------------------------------------------------------------------
     | Default configuration file name
     |--------------------------------------------------------------------------
     */

    'config_file' => env('CONFIG_WRITER', null),

    /*
     |--------------------------------------------------------------------------
     | Strict mode
     |--------------------------------------------------------------------------
     */

    'strict' => true,
];
