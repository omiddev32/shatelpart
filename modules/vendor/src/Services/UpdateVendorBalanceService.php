<?php

namespace App\Vendor\Services;

class UpdateVendorBalanceService
{
	public function updateBalance(string $driver)
	{
        try {
	        $service = new VendorService($driver);
	        $result = $service->getBalance();

	        if($result && property_exists($result, 'balance')) {

	            \DB::table('vendors')->where('service_name', $driver)->update([
	                'balance' => $result->balance,
	                'currency' => $this->getCurrency($result->currency),
	                'updated_at' => now()
	            ]);

	        	return true;
	        }

	        return false;

        } catch (\Exception $e) {
        	return false;
        }
	}

	private function getCurrency(string $iso)
	{
		$currency = \DB::table('currencies')->select('currency_name', 'iso')->where('iso', $iso)->first();
		$currencyName = json_decode($currency->currency_name, true);
		if(isset($currencyName['en']) && $currencyName['en']) {
			return $currencyName['en'];
		}
		return "Dollar";
	}
}