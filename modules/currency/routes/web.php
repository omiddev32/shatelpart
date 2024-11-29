<?php

use Illuminate\Support\Facades\Route;
use App\Currency\Http\Controllers\CurrencyController;
use App\Currency\Services\UpdatePriceService;
use App\Order\Entities\OrderSetting;
use App\Currency\Entities\Currency;

Route::get('/panel-api/boot-currencies', [CurrencyController::class, 'getAndSavedCurrencies'])
	->middleware('nova:api');

Route::get('ff', function() {
	$calc = new \App\Currency\Services\CalculatePriceService;
	$r = $calc->calculation('(P*EX)+ET+(EX*0.5)', 5.06, 66020, 'EX*0.02');
	dd($r);
});

Route::get('/panel-api/update-price', function() {
	$setting = \DB::table('order_settings')->select('currency_api_driver')->first();
	$driver = $setting ? $setting->currency_api_driver : 'NAVASAN';
	$service = new UpdatePriceService($driver);
	$currencies = Currency::where("meta->{$driver}", '!=', '')->get()->pluck('meta', 'id')->toArray();
	$apiService = $service->getPrices();
	$updateList = [];
	foreach($currencies as $id => $currency) {
		$meta = json_decode($currency);
		if(isset($apiService[$meta->{$setting->currency_api_driver}])) {
			$updateList[] = [
				'id' => $id,
				'last_price' => +$apiService[$meta->{$setting->currency_api_driver}]['value'],
				'driver_last_price_update' => $driver,
				'last_price_update' => now(),
			];
				
		}
	}
	Currency::batchUpdate($updateList, 'id');
});