<?php
namespace Kaleidoscope\Tests\Providers;

use Kaleidoscope\Tests\AbstractTestCase;
use Kaleidoscope\Providers\LumenServiceProvider;

class LumenServiceProviderTest extends AbstractTestCase
{

    public function testInstance()
    {
        $provider = new LumenServiceProvider($this->app);
        $provider->boot();

        $this->assertInstanceOf('Kaleidoscope\FeederManager', $this->app['kaleidoscope']);
    }

    public function testBindings()
    {
        $provider = new LumenServiceProvider($this->app);

        // Now make sure bounded
        foreach ($provider->provides() as $binding) {
            $this->assertTrue($this->app->bound($binding));
        }
    }

    public function testPublishConfigure()
    {
        $provider = new LumenServiceProvider($this->app);

        $config = $this->app['config']['kaleidoscope'];

        $expected = [
            'max_size' => 10240,
            'default' => '',
            'storages' => []
        ];

        $this->assertTrue(is_array($config));
        $this->assertSame($expected, $config, 'Should be merge from default config file.');
    }
}
