<?php

namespace App\User\Facades;

use Illuminate\Support\Facades\Facade;

class Otp extends Facade
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
        return 'otp-service'; 
    }
}