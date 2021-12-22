<?php
namespace DimaBzz\LaravelConfigWriter\Tests\Events;

use DimaBzz\LaravelConfigWriter\Events\WriteSuccess;
use DimaBzz\LaravelConfigWriter\Tests\TestCase;
use Exception;
use Illuminate\Support\Facades\Event;

class ConfigWriterEventTest extends TestCase
{
    /**
     * @test
     * @environment-setup usesDefaultName
     */
    public function testSendSuccessEventConfigFile()
    {
        try {
            Event::fake();

            $config = $this->configWriter->write(['url' => 'http://octobercms.com']);
            $this->assertTrue($config);

            $result = include $this->tmpTestConfigFile;

            $this->assertTrue(is_array($result));
            $this->assertArrayHasKey('url', $result);
            $this->assertEquals('http://octobercms.com', $result['url']);

            Event::assertDispatched(WriteSuccess::class, function ($event) {
                return $event->configFile === $this->app['config']['config-writer.config_file'];
            });
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}