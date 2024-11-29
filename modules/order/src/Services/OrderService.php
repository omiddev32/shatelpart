<?php

namespace App\Order\Services;

class OrderService
{
    /**
     * Driver instance
     *
     * @var Api
     */
    protected $driver;

    /**
     * Create a new order service
     * Set the service driver
     *
     * @return void
     */
    public function __construct(string $driverName)
    {
    	switch ($driverName) {
    		case 'cysend':
    			break;
    		    		
    		case 'wallex':
    			break;
    	}
    }
}