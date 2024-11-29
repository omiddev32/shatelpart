<?php

namespace App\Currency\Services;

class NavasanService
{
    /**
     * Token
     *
     * @string $token
     */
    protected $token;    

    /**
     * curl
     *
     * @string $curl
     */
    protected $curl;

    /**
     * Create a new navasan service.
     *
     * @return void
     */
    public function __construct()
    {
    	$this->token = config('currency.services.navasan.token');
    	$this->curl = curl_init();
    }

    public function getPrices()
    {
    	$url = config('currency.services.navasan.url');

		curl_setopt_array($this->curl, array(
		  CURLOPT_URL => "{$url}/?api_key={$this->token}",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		));
		$response = json_decode(curl_exec($this->curl), true);
		curl_close($this->curl);
		return $response;
    }
}