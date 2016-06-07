<?php
namespace Kaleidoscope\Tests\Traits;

use Mockery as m;
use Kaleidoscope\Tests\AbstractTestCase;
use Kaleidoscope\Traits\RerenderImageTrait;

class RerenderImageTraitTest extends AbstractTestCase
{

    use RerenderImageTrait;

    /**
     * @var \Kaleidoscope\Services\AbstractGateway
     */
    protected $service;

    public function setUp()
    {
        parent::setUp();

        $this->service = m::mock('Kaleidoscope\Services\AbstractGateway');
    }

    protected function getStorage()
    {
        return $this->service;
    }

    public function testDefineDelimiter()
    {
        $expected = 'x';

        // First to checking default parameter.
        $this->assertEquals(',', $this->delimiter);

        $this->delimiter = $expected;

        // Should be get custom delimited character.
        $this->assertEquals($expected, $this->delimiter);
    }

    /**
     * @expectedException \ErrorException
     * @expectedExceptionMessage Undefined task name: invalid
     */
    public function testAddHandler()
    {
        $this->addHandler('foo', 'resize');

        $this->assertArrayHasKey('foo', $this->handlers);
        $this->assertSame(['task' => 'resize', 'length' => 2], $this->handlers['foo']);

        $this->addHandler('bar', 'foo|widen');

        $this->assertArrayHasKey('bar', $this->handlers);
        $this->assertSame(['task' => 'foo|widen', 'length' => 3], $this->handlers['bar']);

        // Should be throw error exception.
        $this->addHandler('foobar', 'invalid');
    }

    public function testGetOriginal()
    {
        $request = $this->createRequest(['id' => 1]);

        $this->service->shouldReceive('getRelativePath')
            ->with("kaleidoscope.image", ['id' => 1, 'type' => '', 'input' => '', 'ext' => 'jpg', 'quality' => 90])
            ->andReturn('foo/bar');

        $this->service->shouldReceive('get')
            ->with('foo/bar')
            ->andReturn($this->generateBinaryString());

        $response = $this->getOriginal($request);

        $this->assertInstanceOf('Illuminate\Http\Response', $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse(ctype_print($response->getOriginalContent()));
    }

    public function testGetRender()
    {
        $request = $this->createRequest(['id' => 'foo_bar', 'type' => 'resize']);

        $this->service->shouldReceive('get')
            ->with('foo/bar')
            ->andReturn($this->generateBinaryString());

        $response = $this->getRender($request);

        $this->assertInstanceOf('Illuminate\Http\Response', $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse(ctype_print($response->getOriginalContent()));
    }

    public function testCustomRender()
    {
        // Assign handler with multiple tasks.
        $this->addHandlers(['foobar' => 'resize|widen']);

        // Current "bar" type should be render image as resize and widen functions.
        // So, "input" definition of parsing by (resize)[10, 10] and (widen)[20]
        $request = $this->createRequest(['id' => 'foo_bar', 'type' => 'foobar', 'input' => '10,10,20']);

        $this->service->shouldReceive('get')
            ->with('foo/bar')
            ->andReturn($this->generateBinaryString());

        $response = $this->getRender($request);

        $this->assertInstanceOf('Illuminate\Http\Response', $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse(ctype_print($response->getOriginalContent()));
    }

    protected function createRequest(array $params)
    {
        $request = $this->app['request'];

        $request->setRouteResolver(function () use ($params) {
            $mock = m::mock('Illuminate\Routing\Route');
            $mock->shouldReceive('parameters')->andReturn($params);
            return $mock;
        });

        return $request;
    }

    private function generateBinaryString()
    {
        // Sample 4x4 red rectangle PNG
        return base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAQAAAAECAYAAACp8Z5+AAAAEklEQVQIW2P8z8AARAjASLoAAD+9B/3rBEHIAAAAAElFTkSuQmCC'
        );
    }
}
