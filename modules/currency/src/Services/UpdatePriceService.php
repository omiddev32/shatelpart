<?php

namespace App\Currency\Services;

class UpdatePriceService
{
    /**
     * Driver instance
     *
     * @var Api
     */
    protected $driver;

    /**
     * Create a new update price service.
     *
     * @return void
     */
    public function __construct(string $driverName)
    {
    	switch ($driverName) {
    		case 'NAVASAN':
    			$this->driver = new NavasanService;
    			break;
    		    		
    		case 'PERSIAN_API':
    			$this->driver = new PersianApiService;
    			break;
    		
    		default:
    			$this->driver = new NavasanService;
    			break;
    	}
    }

    public function getPrices()
    {
        return $this->driver->getPrices();
    }
}