<?php

namespace App\Payment\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ApiGatewayService
{
	public function purchase($driver = 'novin', $price = 0, $userId = 1, $description = '', $phoneNumber = '')
	{
		// $response = Http::get('http://gateway1.sharjit.com/api/payment');

		$response = Http::post('https://gateway1.sharjit.com/api/payment', [
		    'driver' => $driver,
		    'price' => $price,
		    'userId' => $userId,
		    'description' => $description,
		    'phoneNumber' => $phoneNumber,
		]);

		$result = json_decode($response->body(), true);

		if(isset($result['driver']) && $result['driver'] === 'novin' && isset($result['transactionId'])) {
			Cache::store('redis')->put("payment-transaction-" . $result['transactionId'], $result, now()->addminutes(3));
		}

		return $response->body();
	}

	public function verfyPayment()
	{

	}
}
