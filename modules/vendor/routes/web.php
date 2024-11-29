<?php

use Illuminate\Support\Facades\Route;
use App\Vendor\Services\{VendorService};
use Illuminate\Http\Request;

use App\Vendor\Drivers\GiftHub\Api as GiftHubApi;
use App\Vendor\Entities\Vendor;
// use Storage;
use App\Vendor\Services\UpdateVendorBalanceService;


Route::get('update-balance', function() {


	app('update-vendor-balance-service')
		->updateBalance('cysend');

	dd(1);

});

Route::get('gift-hub', function() {


// echo $response;


	// \App\Vendor\Jobs\GiftHub\RefreshTokenJob::dispatch()->onConnection('sync');

	// dd(1);


	$service = new GiftHubApi();
	$products = [];
	$variants = [];
	foreach($service->getProducts() as $product) {
		if($product->variants) {
			$variantsIds = [];
			foreach(collect($product->variants)->toArray() as $var) {
				if($var && property_exists($var, 'range')) {
					
					$variants[] = [
			            'product_id' => "gifthub-{$product->productId}",
			            'vendor_id' => 4,
			            'face_value_id' => "gifthub-{$var->sku}",
			            'type' => 'range',
	                    'face_value_currency' => $product->baseCurrency,
	                    'face_value' => $var->range->min,
	                    'max_face_value' => $var->range->max,
	                    'face_value_step' => $var->range->step,
	                    'cost_currency' => 'EUR',
	                    'cost' => $var->range->min,
	                    'max_cost' => $var->range->max,
	                    'promotion' => false,
	                    'created_at' => now(),
	                    'updated_at' => now(),
					];
				} else {
 					$variants[] = [
			            'product_id' => "gifthub-{$product->productId}",
			            'vendor_id' => 4,
			            'face_value_id' => "gifthub-{$var->sku}",
			            'type' => 'fixed',
	                    'face_value_currency' => $product->baseCurrency,
	                    'face_value' => $var->quote,
	                    'definition' => $var->name,
	                    'cost_currency' => 'EUR',
	                    'cost' => $var->quote,
	                    'promotion' => false,
	                    'created_at' => now(),
	                    'updated_at' => now(),
					];
				}
				$variantsIds[] = "gifthub-{$var->sku}";
			}
		}

		$products[] = [
	        'vendor_id' => 4,
	        'zone' => $product->country,
	        'product_id' => "gifthub-{$product->productId}",
	        'name' => $product->name,
	        'description' => json_encode([], true),
	        'logo_url' => Storage::disk('products')->url('Sharjit-Gift-Card-Template.png'),
	        'type' => $product->productType,
	        'promotion' => false,
	        'maintenance' => $product->state !== 'ACTIVE',
	        'beneficiary_information' => json_encode([], true),
	        'usage_instructions' => json_encode([], true),
	        'face_values' => json_encode($variantsIds, true),
	        'countries' => json_encode([], true),
	        'created_at' => now(),
	        'updated_at' => now(),
		];
	}

	dd($products, $variants );


	$product = collect($service->getProducts())->first();
	// dd($product->variants, collect($product->variants)->toArray());




	dd(1);



	dd(collect($service->getProducts())->groupBy('productType'));

	// $vendor = Vendor::whereId(4)->first();

	// $vendor->update([
	// 	'extra_data' => [
	// 		'clientId' => encrypt('YmZlZTU4YjMzYTA2OWM3MDk0NTNmNzJlZTY5MmMzZDY='),
	// 		'clientSecret' => encrypt('eaa1d0519a76a4faf2bc0e4341e5b4c870bd247b3723443fbad8b9a61d203ebc'),
	// 	]
	// ]);

	dd(decrypt(''));

	// dd(encrypt('omid'));


});