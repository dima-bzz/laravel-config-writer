<?php
namespace DimaBzz\LaravelConfigWriter\Tests\Commands;

use DimaBzz\LaravelConfigWriter\Tests\TestCase;

class ConfigWritePublishTest extends TestCase
{
    /**
     * @test
     */
    public function testPublishConfigByTag()
    {
        $path = config_path('config-writer').'.php';

        $this->artisan('vendor:publish', [
            '--tag' => 'config-writer'
        ]);

        $this->assertFileExists($path);

        unlink($path);
    }

    /**
     * @test
     */
    public function testPublishConfigByServiceProvider()
    {
        $path = config_path('config-writer').'.php';

        $this->artisan('vendor:publish', [
            '--provider' => "DimaBzz\LaravelConfigWriter\ServiceProvider"
        ]);

        $this->assertFileExists($path);

        unlink($path);
    }

    /**
     * @test
     */
    public function testPublishConfigForce()
    {
        $path = config_path('config-writer').'.php';

        $this->artisan('vendor:publish', [
            '--tag' => 'config-writer'
        ]);

        $config = $this->configWriter->of(['strict' => false])
            ->configFile('config-writer')
            ->write();

        $this->assertTrue($config);
        $this->assertFileExists($path);

        $this->artisan('vendor:publish', [
            '--tag' => 'config-writer',
            '--force' => true
        ]);

        $this->assertTrue(config('config-writer.strict'));

        unlink($path);
    }
}