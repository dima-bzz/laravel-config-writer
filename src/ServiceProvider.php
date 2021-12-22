<?php

namespace DimaBzz\LaravelConfigWriter;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/config-writer.php'   => config_path('config-writer.php'),
        ]);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('configwriter', ConfigWriter::class);

        $this->mergeConfigFrom(
            __DIR__.'/../config/config-writer.php', 'config-writer'
        );
    }
}
