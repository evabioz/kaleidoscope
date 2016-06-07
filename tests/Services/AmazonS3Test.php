<?php
namespace Kaleidoscope\Tests\Services;

use Mockery as m;
use Kaleidoscope\Tests\AbstractTestCase;

class AmazonS3Test extends AbstractTestCase
{

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testGetResource()
    {
        list($service, $mockClient, $mockBuilder) = $this->createTestService();

        $expected = 'foobar';

        $response = m::mock('Aws\Result');
        $response->shouldReceive('get')
            ->with('Body')
            ->andReturnSelf();
        $response->shouldReceive('__toString')
            ->andReturn($expected);

        // Checking arguments is a array, Also input should be has same keys.
        $mockClient->shouldReceive('getObject')
            ->with([
                'Bucket' => 'foobar',
                'Key' => 'foobar'
            ])
            ->andReturn($response);

        $this->assertEquals($expected, $service->get('foobar'));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState      disabled
     * @expectedException Exception
     * @expectedExceptionMessage {"path":["validation.allowed_types"]}
     */
    public function testStoreWhenValidFail()
    {
        list($service, $mockClient, $mockBuilder) = $this->createTestService(1024, 'doc');

        $tmpPath = tempnam(sys_get_temp_dir(), 'phpunit-');
        rename($tmpPath, "$tmpPath.png");

        try {
            $service->store(['file' => "$tmpPath.png"]);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            // Remove temp file
            unlink("$tmpPath.png");
        }
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState      disabled
     * @expectedException \Kaleidoscope\Exception\RuntimeException
     * @expectedExceptionMessage You can't attach unsaved objects of image type
     */
    public function testStoreWhenSavingFail()
    {
        list($service, $mockClient, $mockBuilder) = $this->createTestService(1024, 'png');

        $tmpPath = tempnam(sys_get_temp_dir(), 'phpunit-');
        rename($tmpPath, "$tmpPath.png");

        $mockBuilder->shouldReceive('save')
            ->with('image', ['type' => 'image'])
            ->andReturn(null);

        try {
            $service->store(['file' => "$tmpPath.png"]);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            // Remove temp file
            unlink("$tmpPath.png");
        }
    }

    public function fileTypeProvider()
    {
        return [
            ['test_1', 'image', 'png', ['path' => 'test_1'], ['path' => 'test_1', 'type' => 'image']],
            ['test_2', 'audio', 'mp3', ['path' => 'test_2'], ['path' => 'test_2', 'type' => 'audio']],
            ['test_3', 'video', 'wmv', ['path' => 'test_3'], ['path' => 'test_3', 'type' => 'video']],
            ['test_4', 'office', 'doc', ['path' => 'test_4'], ['path' => 'test_4', 'type' => 'office']],
            ['test_5', 'other', 'foo', ['path' => 'test_5'], ['path' => 'test_5', 'type' => 'other']]
        ];
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState      disabled
     * @dataProvider             fileTypeProvider
     */
    public function testStoreFromPath($path, $type, $ext, $data, $expected)
    {
        list($service, $mockClient, $mockBuilder) = $this->createTestService(1024, $ext);

        $tmpPath = tempnam(sys_get_temp_dir(), 'phpunit-');
        rename($tmpPath, "$tmpPath.$ext");

        $mockClient->shouldReceive('putObject')
            ->with(m::subset([
                'Bucket' => 'foobar',
                'Key' => $path
            ]));

        $mockBuilder->shouldReceive('save')
            ->with($type, ['type' => $type])
            ->andReturn($data);

        try {
            $beforeStore = function () use ($service) {
                $args = func_get_args();
                $this->assertCount(1, $args);
                $this->assertSame($service, $args[0]);
            };

            $afterStore = function () use ($expected) {
                $args = func_get_args();
                $this->assertCount(2, $args);
                $this->assertTrue($args[0]);
                $this->assertSame($expected, $args[1]);
            };

            $this->assertSame($expected, $service->store(['file' => "$tmpPath.$ext"], $beforeStore, $afterStore));
        } catch (\Exception $e) {
            throw $e;
        } finally {
            // Remove temp file
            unlink("$tmpPath.$ext");
        }
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState      disabled
     */
    public function testStoreFromTmpfile()
    {
        list($service, $mockClient, $mockBuilder) = $this->createTestService(1024, 'png');

        $tmpPath = tempnam(sys_get_temp_dir(), 'phpunit-');

        $mockClient->shouldReceive('putObject')
            ->with(m::subset([
                'Bucket' => 'foobar',
                'Key' => 'foobar'
            ]));

        $mockBuilder->shouldReceive('save')
            ->with('image', ['type' => 'image'])
            ->andReturn(['path' => 'foobar']);

        try {
            $expected = ['path' => 'foobar', 'type' => 'image'];

            $params = [
                'file' => $tmpPath,
                'name' => 'foobar.png'
            ];

            $this->assertSame($expected, $service->store($params));
        } catch (\Exception $e) {
            throw $e;
        } finally {
            // Remove temp file
            unlink($tmpPath);
        }
    }

    protected function createTestService($maxSize = 1024, $allowedTypes = '')
    {
        m::mock('overload:GuzzleHttp\Client');

        $mockClient = m::mock('overload:Aws\AwsClient');
        $mockClient->shouldReceive('factory')->andReturnSelf();

        $mockBuilder = m::mock('overload:Kaleidoscope\StorageBuilder');

        $service = new \Kaleidoscope\Services\AmazonS3();

        $resolver = $this->getMock('\Illuminate\Database\ConnectionResolverInterface');
        $service::setConnectionResolver($resolver);

        $service->initialize([
            'max_size' => $maxSize,
            'types' => $allowedTypes,
            'client_config' => [
                'key' => 'foo',
                'secret' => 'bar',
                'region' => '',
                'version' => 'latest'
            ],
            'object_config' => [
                'Bucket' => 'foobar'
            ]
        ]);

        return [$service, $mockClient, $mockBuilder];
    }
}
