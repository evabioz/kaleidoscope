<?php
namespace Kaleidoscope;

class FeederManager
{

    /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * Gateway Factory Instance
     *
     * @var \Kaleidoscope\Common\GatewayFactory
     */
    protected $factory;

    /**
     * The current gateway to use
     *
     * @var string
     */
    protected $gateway;

    /**
     * The array of resolved queue connections.
     *
     * @var array
     */
    protected $storages = [];

    /**
     * Create a new manager instance.
     *
     * @param \Illuminate\Foundation\Application $app
     * @param
     *            $factory
     */
    public function __construct($app, $factory)
    {
        $this->app = $app;
        $this->factory = $factory;
    }

    /**
     * Get an instance of the specified storage
     *
     * @param
     *            index of config array to use
     * @return \Kaleidoscope\Common\AbstractGateway
     */
    public function storage($name = null)
    {
        if (! isset($this->storages[$name])) {
            $this->storages[$name] = $this->resolve($name);
        }

        return $this->storages[$name];
    }

    protected function resolve($name)
    {
        $config = $this->getConfig("storages.$name");

        if (! $config) {
            throw new \UnexpectedValueException("Storage [$name] is not defined.");
        }

        $storage = $this->factory->create($config['driver']);

        // Get global configure.
        $config = array_merge($config, [
            'max_size' => $this->getConfig('max_size')
        ]);

        $storage->initialize($config);

        return $storage;
    }

    protected function getConfig($name)
    {
        return $this->app['config']["kaleidoscope.$name"];
    }

    protected function getDefaultStorage()
    {
        return $this->app['config']["kaleidoscope.default"];
    }

    /**
     * Handle dynamic method with default storage.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $instance = $this->storage($this->getDefaultStorage());

        return call_user_func_array([$instance, $method], $parameters);
    }
}
