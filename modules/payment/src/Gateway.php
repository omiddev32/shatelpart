<?php

namespace App\Payment;

class Gateway
{
    /**
     * @var class
     */
    public $driver; 

    /**
     * Create a gateway instance.
     *
     * @param $driver
     * @return void
     */
	public function __construct($driver)
	{
        $this->driver = $driver;
	}

    /**
     * Send a request for an initial payment
     * 
     * @param int $amount
     * @param string $referenceNumber
     * @param string $userIdentifier
     * @param string $callbackUrl
     * @param string $currency
     * @param null $description
     * @param $additionalData
     * @return bool|mixed|string
     * @throws Exception
     */
	public function paymentRequest($amount, $referenceNumber, $userIdentifier, $callbackUrl, $description = null, $additionalData = null)
	{
		$request = $this->driver->paymentRequest($amount, $referenceNumber, $userIdentifier, $callbackUrl, 'IRR', $description, $additionalData);

        if(isset($request['errors']) && isset($request['errors'][0])) {

            return [
                'status' => 'Failed',
                'errors' => $request['errors']
            ];
        }

        return [
            'status' => 'Success',
            'redirect' => $request['pspSwitchingUrl'],
            'purchaseId' => $request['purchaseId']
        ]; 
	}

    /**
     * @param $id
     * @return bool|mixed|string
     * @throws Exception
     */
    public function getOrderById($id)
    {
        return $this->driver->getOrderById($id);
    }

    /**
     * @param $purchaseId
     * @return bool|mixed|string
     * @throws Exception
     */
    public function paymentVerify($purchaseId)
    {
        return $this->driver->paymentVerify($purchaseId);
    }

}