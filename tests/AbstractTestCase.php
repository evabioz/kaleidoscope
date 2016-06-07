<?php
namespace Kaleidoscope\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class AbstractTestCase extends OrchestraTestCase
{

    protected $rootPath;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->rootPath = realpath(__DIR__ . '/../src');
    }

    public function setUp()
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            'Kaleidoscope\Providers\LaravelServiceProvider',
            'Kaleidoscope\Providers\LumenServiceProvider'
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Bundle' => 'Kaleidoscope\Facades\FeederFacade'
        ];
    }
}
