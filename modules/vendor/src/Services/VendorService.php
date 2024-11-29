<?php

namespace App\Vendor\Services;

class VendorService
{
    /**
     * Driver instance
     *
     * @var Api
     */
    protected $driver;

    /**
     * Create a new cysend api.
     * Set the service driver
     *
     * @return void
     */
    public function __construct(string $driverName = 'cysend')
    {
    	switch ($driverName) {
    		case 'cysend':
    			$this->driver = new \App\Vendor\Drivers\Cysend\Api;
    			break;
    		    		
    		case 'gifthub':
    			$this->driver = new \App\Vendor\Drivers\GiftHub\Api;
    			break;
    		                      
            case 'wallex':
                $this->driver = new \App\Vendor\Drivers\Wallex\Api;
                break;
            
    		default:
    			$this->driver = new \App\Vendor\Drivers\Cysend\Api;
    			break;
    	}
    }

    /**
     * Get the products list from api
     *
     * @return array
     */
	public function getProducts()
	{
		return $this->driver->getProducts();
	}

    /**
     * This operation returns the CUSTOMER account default balance.
     *
     * @return array
     */
    public function getBalance()
    {
        return $this->driver->getBalance();
    }

    /**
     * Get the Face Values
     *
     * @return array
     */
    public function getFaceValues()
    {
        return $this->driver->getFaceValues();
    }

    /**
     * This operation returns an indicative cost of a face value.
     * 
     * @param $faceValue
     * @param array $beneficiaryInformation = []
     * @param $customFaceValue = null
     * @return array
     */
    public function getCost($faceValue, array $beneficiaryInformation = [], $customFaceValue = null)
    {
        return $this->driver->getCost($faceValue, $beneficiaryInformation, $customFaceValue);
    }

    /**
     *  Place the order through api.
     *
     * @param string $orderId
     * @param $faceValue
     * @param array $beneficiaryInformation => nullable => []
     * @param string $mode => ['live', 'simulate-success', 'simulate-delayed-success', 'simulate-failed', 'simulate-delayed-failed']
     * @param $customFaceValue => for range price => nullable => null
     * @return array
     */
    public function placeOrder($orderId, $faceValue, array $beneficiaryInformation = [], string $mode = 'live', $customFaceValue = null)
    {
        return $this->driver->placeOrder($orderId, $faceValue, $beneficiaryInformation, $mode, $customFaceValue);
    }

    /**
     *  After placing the order, retrieve it.
     *
     * @param string $transactionId
     * @param string $orderId
     * @return array
     */
    public function retrieveOrder(string $uID, string $userUID)
    {
        return $this->driver->retrieveOrder($uID, $userUID);
    }
}
