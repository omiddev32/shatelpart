<?php

namespace App\Vendor\Drivers\Wallex;

class Api
{
    use Product;

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
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected array $header = ['Content-Type: application/json'];

    /**
     * Create a new cysend api.
     *
     * @return void
     */
    public function __construct()
    {
        $this->client = curl_init();
        $this->apiKey = config('cysend.api_key');
    }

    public function getToken()
    {
        return $this->send('POST', 'oauth/token', [
            'username' => config('wallgate.username'),
            'password' => config('wallgate.password'),
        ])->data;
    }

    /**
     * Send request api
     *
     * @param string $method => ['GET', 'POST']
     * @param string $url
     * @param array $body => nullable = [];
     * @return void
     */
    public function send(string $method = 'GET', string $url, array $body = [])
    {
        $path = config('wallgate.base_url');

        $arrayData = array(
          CURLOPT_URL => "{$path}/{$url}",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "{$method}",
          CURLOPT_HTTPHEADER => $this->header,
        );

        if(count($body)) {
            $arrayData[CURLOPT_POSTFIELDS] = json_encode($body);
        }

        curl_setopt_array($this->client, $arrayData);

        $response = curl_exec($this->client);
        $this->closeRequest();
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
    private function setHeader($row)
    {
        $this->header[] = $row;

        return $this;
    }
}
