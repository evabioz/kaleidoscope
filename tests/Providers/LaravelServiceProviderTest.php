<?php
namespace Kaleidoscope\Tests\Providers;

use Mockery as m;
use Kaleidoscope\Tests\AbstractTestCase;
use Kaleidoscope\Providers\LaravelServiceProvider;

class LaravelServiceProviderTest extends AbstractTestCase
{

    public function testInstance()
    {
        $provider = new LaravelServiceProvider($this->app);
        $provider->boot();

        $this->assertInstanceOf('Kaleidoscope\FeederManager', $this->app['kaleidoscope']);
    }

    public function testBindings()
    {
        $provider = new LaravelServiceProvider($this->app);

        // Now make sure bounded
        foreach ($provider->provides() as $binding) {
            $this->assertTrue($this->app->bound($binding));
        }
    }

    public function testPublishConfigure()
    {
        $provider = new LaravelServiceProvider($this->app);

        $paths = $provider::pathsToPublish(get_class($provider), 'config');

        $publishPath = dirname($this->rootPath) . '/config/kaleidoscope.php';
        $releasePath = $this->getBasePath() . '/config/kaleidoscope.php';

        $this->assertCount(1, $paths);
        $this->assertEquals($publishPath, realpath(key($paths)));
        $this->assertEquals($releasePath, reset($paths));
    }

    public function testPublishMigration()
    {
        $provider = new LaravelServiceProvider($this->app);

        $paths = $provider::pathsToPublish(get_class($provider), 'migrations');

        $publishPath = dirname($this->rootPath) . '/database/migrations';
        $releasePath = $this->getBasePath() . '/database/migrations';

        $this->assertCount(1, $paths);
        $this->assertEquals($publishPath, realpath(key($paths)));
        $this->assertEquals($releasePath, reset($paths));
    }
}
