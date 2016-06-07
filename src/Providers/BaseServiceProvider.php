<?php
namespace Kaleidoscope\Providers;

use Illuminate\Support\ServiceProvider;
use Kaleidoscope\Services\AbstractGateway as Gateway;
use Kaleidoscope\StorageFactory;
use Kaleidoscope\FeederManager;

abstract class BaseServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        Gateway::setConnectionResolver($this->app['db']);

        $this->registerManager();
    }

    /**
     * Register the main manager
     */
    public function registerManager()
    {
        $this->app['kaleidoscope'] = $this->app->share(function ($app) {
            $factory = new StorageFactory();
            $manager = new FeederManager($app, $factory);

            return $manager;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'kaleidoscope'
        ];
    }
}
