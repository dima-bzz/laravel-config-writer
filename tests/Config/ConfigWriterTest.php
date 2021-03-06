<?php

namespace DimaBzz\LaravelConfigWriter\Tests\Config;

use DimaBzz\LaravelConfigWriter\Tests\TestCase;
use Exception;

class ConfigWriterTest extends TestCase
{
    /**
     * @test
     * @environment-setup usesNotExistsName
     */
    public function testNotExistConfigFile()
    {
        $file = 'foo';

        try {
            $this->configWriter->write(['connections.sqlite.driver' => 'sqlbite']);
            $this->fail();
        } catch (Exception $e) {
            $this->assertEquals(sprintf('Configuration file %s not found.', $file), $e->getMessage());
        }
    }

    /**
     * @test
     * @environment-setup usesDefaultName
     */
    public function testSetAnotherConfigFile()
    {
        $file = 'bar';

        try {
            $this->configWriter->of(['connections.sqlite.driver' => 'sqlbite'])->configFile($file)->write();
            $this->fail();
        } catch (Exception $e) {
            $this->assertEquals(sprintf('Configuration file %s not found.', $file), $e->getMessage());
        }
    }

    /**
     * @test
     * @environment-setup usesDefaultName
     */
    public function testSetAnotherConfigFileByHelpers()
    {
        $file = 'bar';

        try {
            config_writer($file, ['connections.sqlite.driver' => 'sqlbite']);
            $this->fail();
        } catch (Exception $e) {
            $this->assertEquals(sprintf('Configuration file %s not found.', $file), $e->getMessage());
        }
    }

    /**
     * @test
     * @environment-setup usesDefaultName
     */
    public function testSetAnotherConfigFileWithoutDataByHelpers()
    {
        $file = 'bar';

        try {
            config_writer($file);
            $this->fail();
        } catch (Exception $e) {
            $this->assertEquals('When setting a new value in the config file, you must pass an array of key / value pairs.', $e->getMessage());
        }
    }

    /**
     * @test
     * @environment-setup usesDefaultName
     */
    public function testSetAnotherConfigFileWithDataStringByHelpers()
    {
        $file = 'bar';

        try {
            config_writer($file, 'foo');
            $this->fail();
        } catch (Exception $e) {
            $this->assertEquals('When setting a new value in the config file, you must pass an array of key / value pairs.', $e->getMessage());
        }
    }

    /**
     * @test
     * @environment-setup usesDefaultName
     */
    public function testNotWritableConfigFile()
    {
        try {
            chmod($this->tmpTestConfigFile, 0444);
            $this->configWriter->write(['connections.sqlite.driver' => 'sqlbite']);
            $this->fail();
        } catch (Exception $e) {
            $this->assertFileIsNotWritable($this->tmpTestConfigFile);
            chmod($this->tmpTestConfigFile, 0664);
            $this->assertEquals(sprintf('The config file %s does not support writing.', $this->testConfigFile), $e->getMessage());
        }
    }

    /**
     * @test
     */
    public function testNotSetDefaultConfigFileName()
    {
        try {
            $this->configWriter->write(['connections.sqlite.driver' => 'sqlbite']);
            $this->fail();
        } catch (Exception $e) {
            $this->assertEquals('Default config file name not set.', $e->getMessage());
        }
    }

    /**
     * @test
     * @environment-setup usesDefaultName
     */
    public function testKeyNotExistsConfigFile()
    {
        $key = 'debug';

        try {
            file_put_contents($this->tmpTestConfigFile, '<?php return [];');
            $this->configWriter->write([$key => false]);
        } catch (Exception $e) {
            $this->assertEquals(sprintf('Unable to rewrite key %s in config, does it exist?', $key), $e->getMessage());
        }
    }

    /**
     * @test
     * @environment-setup usesDefaultName
     */
    public function testChangeStringParameterToArrayConfigFile()
    {
        try {
            $this->configWriter->write(['connections.mysql.username' => ['production']]);
            $this->fail();
        } catch (Exception $e) {
            $this->assertEquals('Unable to rewrite key connections.mysql.username in config, write failed.', $e->getMessage());
        }
    }

    /**
     * @test
     * @environment-setup usesDefaultName
     */
    public function testChangeArrayParameterToNullConfigFile()
    {
        try {
            $data = [
                'connections.mysql.driver' => null,
            ];

            $this->configWriter->write($data);
            $this->fail();
        } catch (Exception $e) {
            $this->assertEquals('Unable to rewrite key connections.mysql.driver in config, write failed.', $e->getMessage());
        }
    }

    /**
     * @test
     * @environment-setup usesDefaultName
     */
    public function testChangeBooleanParameterToNullConfigFile()
    {
        try {
            $data = [
                'debug' => null,
            ];

            $this->configWriter->of($data)->strictMode(true)->write();
            $this->fail();
        } catch (Exception $e) {
            $this->assertEquals('Unable to rewrite key debug in config, write failed.', $e->getMessage());
        }
    }

    /**
     * @test
     * @environment-setup usesDefaultName
     */
    public function testEmptyParamConfigFileByHelpers()
    {
        $config = config_writer();
        $this->assertFalse($config);
    }

    /**
     * @test
     * @environment-setup usesDefaultName
     */
    public function testChangeParameterToNullConfigFile()
    {
        try {
            $data = [
                'url' => null,
                'aNumber' => null,
            ];

            $config = $this->configWriter->write($data);
            $this->assertTrue($config);

            $result = include $this->tmpTestConfigFile;

            $this->assertArrayHasKey('url', $result);
            $this->assertArrayHasKey('aNumber', $result);
            $this->assertNull($result['url']);
            $this->assertNull($result['aNumber']);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @environment-setup usesDefaultName
     */
    public function testChangeNullParameterToIntegerConfigFile()
    {
        try {
            $data = [
                'nullable' => 666,
            ];

            $config = $this->configWriter->write($data);
            $this->assertTrue($config);

            $result = include $this->tmpTestConfigFile;

            $this->assertArrayHasKey('nullable', $result);
            $this->assertEquals(666, $result['nullable']);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @environment-setup usesDefaultName
     */
    public function testChangeNullParameterToStringConfigFile()
    {
        try {
            $data = [
                'nullable' => 'foo',
            ];

            $config = $this->configWriter->write($data);
            $this->assertTrue($config);

            $result = include $this->tmpTestConfigFile;

            $this->assertArrayHasKey('nullable', $result);
            $this->assertEquals('foo', $result['nullable']);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @environment-setup usesDefaultName
     */
    public function testChangeStringParameterToBooleanConfigFileStrictModeFalse()
    {
        try {
            $config = $this->configWriter->of(['connections.mysql.host' => false])->strictMode(false)->write();
            $this->assertTrue($config);

            $result = include $this->tmpTestConfigFile;

            $this->assertArrayHasKey('connections', $result);
            $this->assertArrayHasKey('mysql', $result['connections']);
            $this->assertArrayHasKey('host', $result['connections']['mysql']);
            $this->assertFalse($result['connections']['mysql']['host']);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @environment-setup usesDefaultName
     */
    public function testChangeStringParameterToArrayConfigFileStrictModeFalse()
    {
        try {
            $config = $this->configWriter->of(['connections.mysql.username' => ['production']])->strictMode(false)->write();
            $this->assertTrue($config);

            $result = include $this->tmpTestConfigFile;

            $this->assertArrayHasKey('connections', $result);
            $this->assertArrayHasKey('mysql', $result['connections']);
            $this->assertArrayHasKey('username', $result['connections']['mysql']);
            $this->assertIsArray($result['connections']['mysql']['username']);
            $this->assertEquals('production', $result['connections']['mysql']['username'][0]);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @environment-setup usesDefaultName
     */
    public function testChangeParameterConfigFile()
    {
        try {
            $config = $this->configWriter->write(['url' => 'http://octobercms.com']);
            $this->assertTrue($config);

            $result = include $this->tmpTestConfigFile;

            $this->assertTrue(is_array($result));
            $this->assertArrayHasKey('url', $result);
            $this->assertEquals('http://octobercms.com', $result['url']);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @environment-setup usesDefaultName
     */
    public function testChangeParameterConfigFileByHelpers()
    {
        try {
            $config = config_writer(['url' => 'http://octobercms.com']);
            $this->assertTrue($config);

            $result = include $this->tmpTestConfigFile;

            $this->assertTrue(is_array($result));
            $this->assertArrayHasKey('url', $result);
            $this->assertEquals('http://octobercms.com', $result['url']);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @environment-setup usesDefaultName
     */
    public function testChangeSecondLevelParameterConfigFile()
    {
        try {
            $config = $this->configWriter->write(['memcached.host' => '69.69.69.69']);
            $this->assertTrue($config);

            $result = include $this->tmpTestConfigFile;

            $this->assertArrayHasKey('memcached', $result);
            $this->assertArrayHasKey('host', $result['memcached']);
            $this->assertEquals('69.69.69.69', $result['memcached']['host']);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @environment-setup usesDefaultName
     */
    public function testChangeThirdLevelParameterConfigFile()
    {
        try {
            $config = $this->configWriter->write(['connections.mysql.host' => '127.0.0.1']);
            $this->assertTrue($config);

            $result = include $this->tmpTestConfigFile;

            $this->assertArrayHasKey('connections', $result);
            $this->assertArrayHasKey('mysql', $result['connections']);
            $this->assertArrayHasKey('host', $result['connections']['mysql']);
            $this->assertEquals('127.0.0.1', $result['connections']['mysql']['host']);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @environment-setup usesDefaultName
     */
    public function testAlternativeQuotingParameterConfigFile()
    {
        try {
            $data = [
                'timezone' => 'The Fifth Dimension',
                'timezoneAgain' => 'The "Sixth" Dimension',
            ];

            $config = $this->configWriter->write($data);
            $this->assertTrue($config);

            $result = include $this->tmpTestConfigFile;

            $this->assertArrayHasKey('timezone', $result);
            $this->assertArrayHasKey('timezoneAgain', $result);
            $this->assertEquals('The Fifth Dimension', $result['timezone']);
            $this->assertEquals('The "Sixth" Dimension', $result['timezoneAgain']);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @environment-setup usesDefaultName
     */
    public function testChangeBooleanParameterConfigFile()
    {
        try {
            $data = [
                'debug' => false,
                'debugAgain' => true,
                'bullyIan' => 1,
                'booLeeIan' => 0,
                'memcached.weight' => false,
                'connections.pgsql.password' => true,
            ];

            $config = $this->configWriter->write($data);
            $this->assertTrue($config);

            $result = include $this->tmpTestConfigFile;

            $this->assertArrayHasKey('debug', $result);
            $this->assertArrayHasKey('debugAgain', $result);
            $this->assertArrayHasKey('bullyIan', $result);
            $this->assertArrayHasKey('booLeeIan', $result);
            $this->assertFalse($result['debug']);
            $this->assertTrue($result['debugAgain']);
            $this->assertEquals(1, $result['bullyIan']);
            $this->assertEquals(0, $result['booLeeIan']);

            $this->assertArrayHasKey('memcached', $result);
            $this->assertArrayHasKey('weight', $result['memcached']);
            $this->assertFalse($result['memcached']['weight']);

            $this->assertArrayHasKey('connections', $result);
            $this->assertArrayHasKey('pgsql', $result['connections']);
            $this->assertArrayHasKey('password', $result['connections']['pgsql']);
            $this->assertTrue($result['connections']['pgsql']['password']);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @environment-setup usesDefaultName
     */
    public function testChangeIntegerParameterConfigFile()
    {
        try {
            $config = $this->configWriter->write(['aNumber' => 69]);
            $this->assertTrue($config);

            $result = include $this->tmpTestConfigFile;

            $this->assertArrayHasKey('aNumber', $result);
            $this->assertEquals(69, $result['aNumber']);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @environment-setup usesDefaultName
     */
    public function testChangeAssociativeStringArrayParameterConfigFile()
    {
        try {
            $config = $this->configWriter->write(['connections.mysql.driver' => ['rabble' => 'sql']]);
            $this->assertTrue($config);

            $result = include $this->tmpTestConfigFile;

            $this->assertArrayHasKey('connections', $result);
            $this->assertArrayHasKey('mysql', $result['connections']);
            $this->assertArrayHasKey('driver', $result['connections']['mysql']);
            $this->assertIsArray($result['connections']['mysql']['driver']);
            $this->assertArrayHasKey('rabble', $result['connections']['mysql']['driver']);
            $this->assertEquals('sql', $result['connections']['mysql']['driver']['rabble']);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @environment-setup usesDefaultName
     */
    public function testChangeAssociativeIntegerArrayParameterConfigFile()
    {
        try {
            $config = $this->configWriter->write(['connections.pgsql.prefix' => [1 => 'production']]);
            $this->assertTrue($config);

            $result = include $this->tmpTestConfigFile;

            $this->assertArrayHasKey('connections', $result);
            $this->assertArrayHasKey('pgsql', $result['connections']);
            $this->assertArrayHasKey('prefix', $result['connections']['pgsql']);
            $this->assertIsArray($result['connections']['pgsql']['prefix']);
            $this->assertEquals('production', $result['connections']['pgsql']['prefix'][1]);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @environment-setup usesDefaultName
     */
    public function testChangeArrayParameterConfigFile()
    {
        try {
            $config = $this->configWriter->write(['connections.sqlsrv.prefix' => ['production']]);
            $this->assertTrue($config);

            $result = include $this->tmpTestConfigFile;

            $this->assertArrayHasKey('connections', $result);
            $this->assertArrayHasKey('sqlsrv', $result['connections']);
            $this->assertArrayHasKey('prefix', $result['connections']['sqlsrv']);
            $this->assertIsArray($result['connections']['sqlsrv']['prefix']);
            $this->assertEquals('production', $result['connections']['sqlsrv']['prefix'][0]);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @environment-setup usesDefaultName
     */
    public function testChangeEmptyArrayParameterConfigFile()
    {
        try {
            $config = $this->configWriter->write(['connections.sqlsrv.prefix' => []]);
            $this->assertTrue($config);

            $result = include $this->tmpTestConfigFile;

            $this->assertArrayHasKey('connections', $result);
            $this->assertArrayHasKey('sqlsrv', $result['connections']);
            $this->assertArrayHasKey('prefix', $result['connections']['sqlsrv']);
            $this->assertIsArray($result['connections']['sqlsrv']['prefix']);
            $this->assertEquals(0, count($result['connections']['sqlsrv']['prefix']));
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @environment-setup usesDefaultName
     */
    public function testChangeEmptyParameterConfigFile()
    {
        try {
            $config = $this->configWriter->write(['connections.sqlsrv.password' => '123456']);
            $this->assertTrue($config);

            $result = include $this->tmpTestConfigFile;

            $this->assertArrayHasKey('connections', $result);
            $this->assertArrayHasKey('sqlsrv', $result['connections']);
            $this->assertArrayHasKey('password', $result['connections']['sqlsrv']);
            $this->assertEquals('123456', $result['connections']['sqlsrv']['password']);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}
