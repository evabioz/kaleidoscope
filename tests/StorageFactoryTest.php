<?php
namespace Kaleidoscope\Tests;

use Mockery as m;
use Kaleidoscope\Tests\AbstractTestCase;
use Kaleidoscope\StorageFactory;

class StorageFactoryTest extends AbstractTestCase
{

    /**
     * @expectedException \Kaleidoscope\Exception\RuntimeException
     * @expectedExceptionMessage Class '\Foo\Boo' not found.
     */
    public function testNotExistStorage()
    {
        $factory = new StorageFactory;

        $factory->create('\Foo\Boo');
    }

    public function testCreateStorage()
    {
        $mock = m::mock('stdClass, Kaleidoscope\Services\AbstractGateway', [null]);

        $factory = new StorageFactory;

        $this->assertInstanceOf(get_class($mock), $factory->create(get_class($mock)));
    }
}
