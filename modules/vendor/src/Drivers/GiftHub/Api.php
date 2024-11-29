<?php

namespace App\Vendor\Drivers\GiftHub;

use Illuminate\Support\Facades\Cache;

class Api
{
    use Token, Product;
    
    /**
     * client
     *
     * @var
     */
    protected $client;

    /**
     * Api key.
     *
     * @var string
     */
    protected $apiKey;

    /**
     * Create a new gifthub api.
     *
     * @return void
     */
    public function __construct($ignoreRedis = false)
    {
        $this->client = curl_init();
        $token = '';

        if(Cache::store('redis')->has('gifthub-vendor-token') && ! $ignoreRedis) {
            $token = Cache::store('redis')->get('gifthub-vendor-token');
        } else {
            $vendor = \DB::table('vendors')
                ->select(['service_name', 'token'])
                ->where('service_name', 'gifthub')
                ->first();
                if($vendor->token) {
                    Cache::store('redis')->put('gifthub-vendor-token', $vendor->token, now()->addDays(365));
                    $token = $vendor->token;
                }
        }

        if($token) {
            $this->apiKey = decrypt($token);
        }
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
    public function send(string $method = 'GET', string $url, string $body = '', $addToHeader = [], bool $headerResponse = false)
    {
        $path = config('gifthub.base_url');

        curl_setopt_array($this->client, array(
          CURLOPT_URL => "{$path}/{$url}",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_HEADER => $headerResponse,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "{$method}",
          CURLOPT_POSTFIELDS => "{$body}",
          CURLOPT_HTTPHEADER => $this->header($addToHeader, $headerResponse),
        ));

        $response = curl_exec($this->client);
        $this->closeRequest();

        /* Only For Refresh Token*/
        if($headerResponse) {
            list($header, $body) = explode("\r\n\r\n", $response, 2);
            $headers = explode("\r\n", $header);
            $authorization = null;
            foreach ($headers as $hdr) {
                if (strpos($hdr, 'Authorization: Bearer ') !== false) {
                    $authorization = trim(str_replace('Authorization: Bearer ', '', $hdr));
                    break;
                }
            }
            return $authorization;
        }

        return json_decode($response);
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
    private function header(array $addToHeader = [], bool $headerResponseMode = false)
    {
        if(count($addToHeader) && $headerResponseMode) {
            return $addToHeader;
        } else {
            $defaultHeader = array(
                'accept: application/json',
                "Authorization: Bearer {$this->apiKey}"
            );
        }

        return count($addToHeader) ? array_merge($defaultHeader, $addToHeader) : $defaultHeader;
    }
}
