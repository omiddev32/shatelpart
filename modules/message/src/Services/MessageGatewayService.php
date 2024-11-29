<?php

namespace App\Message\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class MessageGatewayService
{
    /**
     * @var string
     */
	private string $gatewayToken;

    /**
     * @var string
     */
    public string $server;

    /**
     * @var string
     */
    public string $phone;

    /**
     * @var string
     */
    public string $text;    

    /**
     * @var string
     */
    public string $driver;

    /**
     * Create a melipayamak service instance.
     *
     * @return void
     */
	public function __construct()
	{
        $this->gatewayToken = config('message.verify_token');
        $this->server = config('message.server_gateway');
        $this->driver = config('message.driver');
	}

    /**
     * Send message
     *
     * @return boolean
     */
    public function send()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->gatewayToken}"
            ])->post("{$this->server}/api/messages/send", [
                'driver' => $this->driver,
                'phone' => $this->to,
                'text' => $this->text
            ]);

            return json_decode($response);
        } catch(Exception $e){
            dd($e->getMessage());
        }
    }

    /**
     * Set to
     *
     * @param string $phone
     * @return $this
     */
    public function to(string $phone)
    {
        $this->to = $phone;
        return $this;
    }

    /**
     * Set driver
     *
     * @param string $driver
     * @return $this
     */
    public function setDriver(string $driver)
    {
        $this->driver = $driver;
        return $this;
    }

    /**
     * Set text
     *
     * @return $this
     */
    public function text($text)
    {
        $this->text = $text;
        return $this;
    }
}
