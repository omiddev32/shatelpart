<?php

use Illuminate\Support\Facades\Route;
use App\Order\Http\Controllers\Api\{OrderController, AddToOrderController, OrderPaymentController, OrderReceiptController};

Route::middleware('auth:api')->prefix('orders')->group(function() {

	Route::post('/add-to-order', [AddToOrderController::class, 'addToOrder']);
	Route::post('/order-receipt/{orderId}', [OrderReceiptController::class, 'orderReceipt']);
	Route::post('/updte-order-receipt/{orderId}', [OrderReceiptController::class, 'updateOrderReceipt']);
	Route::post('/order-payment', [OrderPaymentController::class, 'orderPayment']);

	Route::post('/', [OrderController::class, 'getOrders']);
	Route::post('/{orderId}', [OrderController::class, 'getOrderDetail']);

});