<?php

namespace ZBrettonYe\HCaptcha\Facades;

use Illuminate\Support\Facades\Facade;

class HCaptcha extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'HCaptcha';
    }
}
