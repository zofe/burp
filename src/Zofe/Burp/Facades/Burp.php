<?php namespace Zofe\Burp\Facades;

use Illuminate\Support\Facades\Facade;

class Burp extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'Zofe\Burp\Burp'; }

}
