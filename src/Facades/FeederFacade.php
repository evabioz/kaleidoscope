<?php
namespace Kaleidoscope\Facades;

use Illuminate\Support\Facades\Facade;

/**
 *
 * @see \Illuminate\Events\Dispatcher
 */
class FeederFacade extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'kaleidoscope';
    }
}
