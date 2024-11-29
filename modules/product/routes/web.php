<?php

use Illuminate\Support\Facades\Route;
use App\Product\Http\Controllers\Api\Cysend\{ProductController, FaceValueController};
use App\Product\Http\Controllers\Api\Wallex\ProductController as WallexProductController;
use App\Product\Http\Controllers\Panel\ProductController as PanelProductController;
use App\Product\Entities\Product;
use App\Product\Entities\ProductApi;
use App\Product\Entities\FaceValueApi;
use Illuminate\Support\Facades\Cache;

Route::prefix('api')->group(function() {

	Route::prefix('cysend')->group(function() {
		Route::get('get-products', [ProductController::class, 'getProducts']);
		// Route::get('get-face-values', [FaceValueController::class, 'getFaceValues']);
	});

	Route::prefix('wallex')->group(function() {
		Route::get('get-products', [WallexProductController::class, 'getProducts']);
	});

	// Route::get('update-product-id/{vendorId}', function($vendorId) {
	// 	$vendorName = $vendorId == 2 ? 'cysend' : 'wallex';
	// 	foreach(ProductApi::where('vendor_id', $vendorId)->get()->chunk(200) as $products) {
	// 		foreach($products as $product) {
	// 			$product->update([
	// 				'product_id' => "{$vendorName}-{$product->product_id}"
	// 			]);
	// 		}
	// 	}
	// });

	// Route::get('update-face-value-apis-product-id/{vendorId}', function($vendorId) {
	// 	$vendorName = $vendorId == 2 ? 'cysend' : 'wallex';
	// 	foreach(FaceValueApi::where('vendor_id', $vendorId)->get()->chunk(200) as $values) {
	// 		foreach($values as $value) {
	// 			$value->update([
	// 				'product_id' => "{$vendorName}-{$value->product_id}"
	// 			]);
	// 		}
	// 	}
	// });

});


Route::post('/panel-api/products/provided', [PanelProductController::class, 'providedProducts'])
	->middleware('nova:api');