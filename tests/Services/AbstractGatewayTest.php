<?php
namespace Kaleidoscope\Tests\Services;

use Kaleidoscope\Tests\AbstractTestCase;

class AbstractGatewayTest extends AbstractTestCase
{

    /**
     *
     * @var \Kaleidoscope\Services\AbstractGateway
     */
    protected $gateway;

    public function setUp()
    {
        parent::setUp();

        $this->gateway = $this->getMockForAbstractClass('Kaleidoscope\Services\AbstractGateway');
    }

    public function configesProvider()
    {
        $data1 = [
            'foo',
            [
                'path' => ['foo' => 'path/without/replace']
            ],
            [],
            'path/without/replace'
        ];

        $data2 = [
            'bar',
            [
                'path' => ['bar' => 'path/replace/:name']
            ],
            [
                'name' => 'foobar'
            ],
            'path/replace/foobar'
        ];

        return [$data1, $data2];
    }

    /**
     * @dataProvider configesProvider
     */
    public function testDefaultParams($path, $params, $replaces, $expected)
    {
        $this->gateway->initialize($params);

        $this->assertArrayHasKey('path', $this->gateway->getParameters());
        $this->assertSame($params, $this->gateway->getParameters());
    }

    /**
     * @dataProvider configesProvider
     */
    public function testGetRelativePath($path, $params, $replaces, $expected)
    {
        $this->gateway->initialize($params);

        $path = $this->gateway->getRelativePath($path, $replaces);

        $this->assertEquals($expected, $path);
    }

    public function testCreateBuilder()
    {
        $gateway = $this->gateway;

        $resolver = $this->getMock('\Illuminate\Database\ConnectionResolverInterface');
        $gateway::setConnectionResolver($resolver);

        $bulider = $gateway->newBuilder();

        $this->assertInstanceOf('\Kaleidoscope\StorageBuilder', $bulider);
    }

    /**
     * @expectedException BadMethodCallException
     * @expectedExceptionMessage Call to undefined method foobar
     */
    public function testInvalidMethod()
    {
        call_user_func([$this->gateway, 'foobar']);
    }
}
