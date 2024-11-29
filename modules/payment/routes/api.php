<?php

use Illuminate\Support\Facades\Route;
use App\Payment\Http\Controllers\Api\{PaymentController, TransactionController, RedirectToBankController};

Route::post('/payments/verify-payment', [PaymentController::class, 'verifyRequest']);
Route::get('/payment/test', [PaymentController::class, 'payment']);


Route::middleware('auth:api')->group(function() {
	Route::post('/payments/result', [PaymentController::class, 'paymentResult']);

	/* Only For Novin Driver */
	Route::get('/payments/redirect-to-bank/{token}', [RedirectToBankController::class, 'redirect']);

	Route::post('/transactions', [TransactionController::class, 'transactionsList']);
});
