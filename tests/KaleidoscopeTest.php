<?php
namespace Kaleidoscope\Tests;

use Mockery as m;
use Kaleidoscope\Tests\AbstractTestCase;

class KaleidoscopeTest extends AbstractTestCase
{

    /**
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessage Storage [invalid] is not defined.
     */
    public function testNotExistStorage()
    {
        $manager = $this->createManager(m::mock('stdClass'));

        $manager->storage('invalid');
    }

    public function testDefaultStorage()
    {
        // Below, should be use service interface, but we don't defined that.
        // So, just create a fake service interface with current namespace.
        $mock = m::mock('overload:Foo\Bar');
        $mock->shouldReceive('initialize')
            ->with([
                'driver' => get_class($mock),
                'max_size' => 1024
            ]);
        $mock->shouldReceive('foobar')
            ->andReturn(true);

        $manager = $this->createManager($mock);

        $this->assertTrue($manager->__call('foobar', []));
    }

    public function testSomeoneStorage()
    {
        // Below, should be use service interface, but we don't defined that.
        // So, just create a fake service interface with current namespace.
        $mock = m::mock('overload:Foo\Bar');
        $mock->shouldReceive('initialize')
            ->with([
                'driver' => get_class($mock),
                'max_size' => 1024
            ]);

        $manager = $this->createManager($mock);
        $service = $manager->storage('foobar');

        $this->assertInstanceOf(get_class($mock), $service);
    }

    /**
     * @param $mock \Kaleidoscope\Services\{ ... }
     * @return \Kaleidoscope\FeederManager
     */
    protected function createManager($mock)
    {
        // Set requirement configuration.
        $config = $this->app['config'];
        $config->set('kaleidoscope.default', 'foobar');
        $config->set('kaleidoscope.max_size', 1024);
        $config->set('kaleidoscope.storages.foobar', [
            'driver' => get_class($mock)
        ]);

        return $this->app['kaleidoscope'];
    }
}
