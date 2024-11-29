<?php

namespace App\Vendor\Facades;

use Illuminate\Support\Facades\Facade;

class UpdateVendorBalance extends Facade
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
        return 'update-vendor-balance-service'; 
    }
}