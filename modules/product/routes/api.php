<?php

use Illuminate\Support\Facades\Route;
use App\Product\Http\Controllers\Api\{ProductController, ProductVariantController, TorobController};

/* Torob Api */
Route::prefix('torob')->group(function() {
	Route::post('/products', [TorobController::class, 'getProducts']);
});

/* Front Api */
Route::prefix('products')->group(function() {

	Route::controller(ProductController::class)->group(function() {
		Route::post('/', 'getProducts');
		Route::post('/search', 'productSearch');
		Route::post('/{productId}', 'getProduct');
	});

	Route::post('/variant/{variantId}', [ProductVariantController::class, 'getProductVariant']);

});