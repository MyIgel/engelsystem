<?php

namespace Engelsystem\Test\Unit\Config;

use Engelsystem\Application;
use Engelsystem\Config\Config;
use Engelsystem\Config\ConfigServiceProvider;
use Engelsystem\Models\EventConfig;
use Engelsystem\Test\Unit\ServiceProviderTest;
use Exception;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit_Framework_MockObject_MockObject;

class ConfigServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Config\ConfigServiceProvider::register
     * @covers \Engelsystem\Config\ConfigServiceProvider::getConfigPath
     */
    public function testRegister()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|Config $config */
        $config = $this->getMockBuilder(Config::class)
            ->getMock();

        $app = $this->getApp(['make', 'instance', 'get']);
        Application::setInstance($app);

        $this->setExpects($app, 'make', [Config::class], $config);
        $this->setExpects($app, 'get', ['path.config'], __DIR__ . '/../../../config', $this->atLeastOnce());
        $app->expects($this->exactly(2))
            ->method('instance')
            ->withConsecutive(
                [Config::class, $config],
                ['config', $config]
            );

        $this->setExpects($config, 'set', null, null, $this->exactly(2));
        $config->expects($this->exactly(3))
            ->method('get')
            ->with(null)
            ->willReturnOnConsecutiveCalls([], [], ['lor' => 'em']);

        $configFile = __DIR__ . '/../../../config/config.php';
        $configExists = file_exists($configFile);
        if (!$configExists) {
            file_put_contents($configFile, '<?php return ["lor"=>"em"];');
        }

        $serviceProvider = new ConfigServiceProvider($app);
        $serviceProvider->register();

        if (!$configExists) {
            unlink($configFile);
        }
    }

    /**
     * @covers \Engelsystem\Config\ConfigServiceProvider::register()
     */
    public function testRegisterException()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|Config $config */
        $config = $this->getMockBuilder(Config::class)
            ->getMock();

        $app = $this->getApp(['make', 'instance', 'get']);
        Application::setInstance($app);

        $this->setExpects($app, 'make', [Config::class], $config);
        $app->expects($this->exactly(2))
            ->method('instance')
            ->withConsecutive(
                [Config::class, $config],
                ['config', $config]
            );
        $this->setExpects($app, 'get', ['path.config'], __DIR__ . '/not_existing', $this->atLeastOnce());

        $this->setExpects($config, 'set', null, null, $this->never());
        $this->setExpects($config, 'get', [null], []);

        $this->expectException(Exception::class);

        $serviceProvider = new ConfigServiceProvider($app);
        $serviceProvider->register();
    }

    /**
     * @covers \Engelsystem\Config\ConfigServiceProvider::__construct
     * @covers \Engelsystem\Config\ConfigServiceProvider::boot
     */
    public function testBoot()
    {
        $app = $this->getApp(['get']);

        /** @var EventConfig|MockObject $eventConfig */
        $eventConfig = $this->createMock(EventConfig::class);
        /** @var Connection|MockObject $connection */
        $connection = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var SchemaBuilder|MockObject $schemaBuilder */
        $schemaBuilder = $this->getMockBuilder(SchemaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var EloquentBuilder|MockObject $eloquentBuilder */
        $eloquentBuilder = $this->getMockBuilder(EloquentBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $config = new Config(['foo' => 'bar', 'lorem' => ['ipsum' => 'dolor', 'bla' => 'foo']]);

        $configs = [
            new EventConfig(['name' => 'test', 'value' => 'testing']),
            new EventConfig(['name' => 'lorem', 'value' => ['ipsum' => 'tester']]),
        ];

        $this->setExpects($eventConfig, 'getConnection', null, $connection, $this->exactly(2));
        $this->setExpects($eventConfig, 'newQuery', null, $eloquentBuilder);
        $this->setExpects($eloquentBuilder, 'get', [['name', 'value']], $configs);
        $this->setExpects($connection, 'getSchemaBuilder', null, $schemaBuilder, $this->exactly(2));

        $schemaBuilder->expects($this->exactly(2))
            ->method('hasTable')
            ->willReturnOnConsecutiveCalls(false, true);

        $this->setExpects($app, 'get', ['config'], $config);

        $serviceProvider = new ConfigServiceProvider($app);
        $serviceProvider->boot();

        $serviceProvider = new ConfigServiceProvider($app, $eventConfig);
        $serviceProvider->boot();
        $serviceProvider->boot();

        $this->assertArraySubset(
            [
                'foo'   => 'bar',
                'lorem' => [
                    'ipsum' => 'tester',
                    'bla'   => 'foo',
                ],
                'test'  => 'testing',
            ],
            $config->get(null)
        );
    }
}
