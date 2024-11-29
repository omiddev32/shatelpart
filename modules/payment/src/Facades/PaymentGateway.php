<?php

namespace App\Payment\Facades;

use Illuminate\Support\Facades\Facade;

class PaymentGateway extends Facade
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
        return 'payment-gateway'; 
    }
}