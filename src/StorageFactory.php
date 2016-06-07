<?php
namespace Kaleidoscope;

use Guzzle\Http\ClientInterface;
use Kaleidoscope\Exception\RuntimeException;
use Kaleidoscope\Services\AbstractGateway;

class StorageFactory
{

    /**
     * Create a new storage instance
     *
     * @param string $class
     *            Gateway name
     * @param ClientInterface|null $httpClient
     *            A Guzzle HTTP Client implementation
     * @throws RuntimeException If no such gateway is found
     * @return object An object of class $class is created and returned
     */
    public function create($class, ClientInterface $httpClient = null)
    {
        if (!class_exists($class)) {
            throw new RuntimeException("Class '$class' not found.");
        }

        return new $class($httpClient);
    }
}
