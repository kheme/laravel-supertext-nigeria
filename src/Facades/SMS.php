<?php

namespace Kheme\SuperTextNg\Facades;
use Illuminate\Support\Facades\Facade;

class SMS extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        return 'SMS';
    }
}