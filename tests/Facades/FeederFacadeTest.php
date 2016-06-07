<?php
namespace Kaleidoscope\Tests\Facades;

use Kaleidoscope\Tests\AbstractTestCase;
use Kaleidoscope\Facades\FeederFacade as Kaleidoscope;

class FeederFacadeTest extends AbstractTestCase
{

    public function testFacadeInstance()
    {
        $this->assertInstanceOf('Kaleidoscope\FeederManager', Kaleidoscope::getFacadeRoot());
    }
}
