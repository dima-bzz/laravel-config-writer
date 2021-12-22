<?php

namespace DimaBzz\LaravelConfigWriter\Tests;

use DimaBzz\LaravelConfigWriter\ConfigWriter;
use DimaBzz\LaravelConfigWriter\ServiceProvider;
use Exception;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected string $testConfigFile = 'sample-config.php';

    protected string $tmpTestConfigFile;

    protected ConfigWriter $configWriter;

    protected function setUp(): void
    {
        parent::setUp();

        $file = __DIR__.'/fixtures/Config/'.$this->testConfigFile;
        $this->tmpTestConfigFile = config_path($this->testConfigFile);

        clearstatcache();

        if (! copy($file, $this->tmpTestConfigFile)) {
            throw new Exception('File not copied');
        }

        $this->configWriter = new ConfigWriter();
    }

    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }

    protected function usesDefaultName($app)
    {
        $app->config->set('config-writer.name', $this->testConfigFile);
    }

    protected function usesNotExistsName($app)
    {
        $app->config->set('config-writer.name', 'foo');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unlink($this->tmpTestConfigFile);
    }
}
