<?php

namespace App\Vendor\Drivers\Cysend;

trait Token
{
    /**
     * Get the new token
     *
     * @return array
     */
	public function refreshToken()
	{
        $body = json_encode([
            "ip_restricted" => true,
            "authorized_ips" => "mine",
            "TTL" => 3600
        ]);

		return $this->send('POST', "/access/token", $body, array('Content-Type: application/json'));
	}
}