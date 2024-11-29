<?php

namespace App\Message\Facades;

use Illuminate\Support\Facades\Facade;

class Message extends Facade
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
        return 'message-service'; 
    }
}