<?php
namespace Kaleidoscope\Services;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Arr;
use Illuminate\Database\ConnectionResolverInterface as Resolver;
use Kaleidoscope\Exception\RuntimeException;
use Kaleidoscope\StorageBuilder;

abstract class AbstractGateway
{

    /**
     * The connection resolver instance.
     *
     * @var \Illuminate\Database\ConnectionResolverInterface
     */
    protected static $resolver;

    /**
     * Defined parameters
     *
     * @var array
     */
    protected $parameters;

    /**
     * Http client api
     *
     * @var \Guzzle\Http\ClientInterface
     */
    protected $httpClient;

    /**
     * Create a new gateway instance
     *
     * @param ClientInterface $httpClient
     *            A Guzzle client to make API calls with
     * @param HttpRequest $httpRequest
     *            A Symfony HTTP request object
     */
    public function __construct(ClientInterface $httpClient = null)
    {
        $this->parameters = [];
        $this->httpClient = $httpClient ?  : $this->getDefaultHttpClient();
    }

    public function initialize(array $parameters = array())
    {
        // set default parameters
        foreach ($this->getDefaultParameters() as $key => $value) {
            Arr::set($this->parameters, $key, $value);
        }

        $this->parameters = array_merge($this->parameters, $parameters);
    }

    protected function getDefaultParameters()
    {
        return array();
    }

    /**
     * Resolve a connection instance.
     *
     * @param string|null $connection
     * @return \Illuminate\Database\Connection
     */
    public static function resolveConnection($connection = null)
    {
        return static::$resolver->connection($connection);
    }

    /**
     * Set the connection resolver instance.
     *
     * @param \Illuminate\Database\ConnectionResolverInterface $resolver
     * @return void
     */
    public static function setConnectionResolver(Resolver $resolver)
    {
        static::$resolver = $resolver;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getParameter($key, $default = null)
    {
        return Arr::get($this->parameters, $key, $default);
    }

    public function setParameter($key, $value)
    {
        Arr::set($this->parameters, $key, $value);

        return $this;
    }

    /**
     * Get the global default HTTP client.
     *
     * @return HttpClient
     */
    protected function getDefaultHttpClient()
    {
        return new HttpClient(array(
            'curl.options' => array(
                CURLOPT_CONNECTTIMEOUT => 60
            )
        ));
    }

    /**
     * Get path, find and replace attributes in the line, if detected.
     *
     * @param string $traget
     * @param array $parameters
     * @return string
     */
    public function getRelativePath($traget, array $parameters = [])
    {
        $path = $this->getParameter("path")[$traget];

        return $this->makeReplacements($path, $parameters);
    }

    /**
     * Make the place-holder replacements.
     *
     * @param string $str
     * @param array $replace
     * @return string
     */
    protected function makeReplacements($str, array $replace)
    {
        if (preg_match_all('/:(\w+\.?\w+)/', $str, $matches)) {
            foreach ($matches[1] as $key) {
                $value = Arr::get($replace, $key, $this->getParameter($key));
                $str = str_replace_once(':' . $key, $value, $str);
            }
        }

        return $str;
    }

    /**
     *
     * @param string $type
     * @return array
     */
    protected function performSave($type, array $values = [])
    {
        $values['type'] = $type;

        $responseData = $this->newBuilder()->save($type, $values);

        if (is_null($responseData)) {
            throw new RuntimeException(sprintf("You can't attach unsaved objects of %s type", $type));
        }

        return array_merge($responseData, $values);
    }

    /**
     * Get a new query builder for the model's table.
     *
     * @return \Kaleidoscope\StorageBuilder
     */
    public function newBuilder()
    {
        $connection = static::resolveConnection();

        return new StorageBuilder($this, $connection);
    }

    /**
     * Handle dynamic method calls into the storage.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        throw new \BadMethodCallException("Call to undefined method $method");
    }
}
