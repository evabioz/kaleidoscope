<?php
namespace Kaleidoscope\Tests;

use Mockery as m;
use Kaleidoscope\Tests\AbstractTestCase;
use Kaleidoscope\StorageBuilder;

class StorageBuilderTest extends AbstractTestCase
{

    /**
     * @var \Kaleidoscope\Services\AbstractGateway
     */
    protected $service;

    /**
     * @var \Illuminate\Database\Connection
     */
    protected $connection;

    public function setUp()
    {
        parent::setUp();

        $this->service = m::mock('Kaleidoscope\Services\AbstractGateway');
        $this->connection = m::mock('Illuminate\Database\Connection');
    }

    public function testRaw()
    {
        $expected = [
            'foo' => 'bar'
        ];

        $builder = m::mock('Illuminate\Database\Query\Builder');
        $builder->shouldReceive('find')
            ->with(1, ['*'])
            ->andReturn($expected);

        $this->connection->shouldReceive('table')
            ->with('kaleidoscope')
            ->andReturn($builder);

        $sb = new StorageBuilder($this->service, $this->connection);

        $this->assertSame($expected, $sb->raw(1)->all());
    }

    public function testSave()
    {
        $expected = [
            'id' => 1,
            'path' => 'foo/bar'
        ];

        $this->service->shouldReceive('getRelativePath')
            ->with('kaleidoscope.foobar', ['id' => 1])
            ->andReturn('foo/bar');

        $builder = m::mock('Illuminate\Database\Query\Builder');
        $builder->shouldReceive('insertGetId')
            ->with(m::type('array'))
            ->andReturn(1);
        $builder->shouldReceive('where', 'update')
            ->andReturnSelf();

        $this->connection->shouldReceive('table')
            ->with('kaleidoscope')
            ->andReturn($builder);

        $this->connection->shouldReceive('transaction')
            ->with(m::on(function (\Closure $closure) use ($expected) {
                $this->assertSame($expected, $closure());
                return true;
            }));

        $sb = new StorageBuilder($this->service, $this->connection);
        $sb->save('foobar', ['foo' => 'bar']);
    }

    /**
     * @expectedException BadMethodCallException
     * @expectedExceptionMessage Call to undefined method foobar
     */
    public function testInvalidMethod()
    {
        $builder = new StorageBuilder($this->service, $this->connection);

        call_user_func([$builder, 'foobar']);
    }
}
