<?php

namespace App\Vendor\Drivers\Cysend;

use Illuminate\Support\Facades\Cache;

class Api
{
    use Product, FaceValue, Order, Token;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $client;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $apiKey;

    /**
     * Create a new cysend api.
     *
     * @return void
     */
    public function __construct($ignoreRedis = false)
    {
        $this->client = curl_init();
        $token = '';

        if(Cache::store('redis')->has('cysend-vendor-token') && ! $ignoreRedis) {
            $token = Cache::store('redis')->get('cysend-vendor-token');
        } else {
            $vendor = \DB::table('vendors')
                ->select(['service_name', 'token'])
                ->where('service_name', 'cysend')
                ->first();
            Cache::store('redis')->put('cysend-vendor-token', $vendor->token, now()->addDays(365));
            $token = $vendor->token;
        }

        $this->apiKey = decrypt($token);
    }

    /**
     * Send request api
     *
     * @param string $method => ['GET', 'POST']
     * @param string $url
     * @param json $body => '';
     * @param array $addToHeader => nullable = [];
     * @return void
     */
    public function send(string $method = 'GET', string $url, string $body = '', $addToHeader = [])
    {
        $path = config('cysend.base_url');

        curl_setopt_array($this->client, array(
          CURLOPT_URL => "{$path}/{$url}",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "{$method}",
          CURLOPT_POSTFIELDS => "{$body}",
          CURLOPT_HTTPHEADER => $this->header($addToHeader),
        ));

        $response = curl_exec($this->client);
        $this->closeRequest();
        return json_decode($response);
    }

    /**
     * This operation returns the CUSTOMER account default balance.
     *
     * @return array
     */
    public function getBalance()
    {
        return $this->send('GET', "/customer/balance");
    }

    /**
     * Close api request
     *
     * @return void
     */
    private function closeRequest()
    {
        curl_close($this->client);
    }

    /**
     * Header api
     *
     * @return array
     */
    private function header(array $addToHeader = [])
    {
        $defaultHeader = array(
            'accept: application/json',
            "Authorization: Basic {$this->apiKey}"
        );

        return count($addToHeader) ? array_merge($defaultHeader, $addToHeader) : $defaultHeader;
    }
}
