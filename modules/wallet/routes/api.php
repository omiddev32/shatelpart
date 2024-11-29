<?php

use Illuminate\Support\Facades\Route;
use App\Wallet\Http\Controllers\Api\RechargeWalletController;

Route::post('/user/recharge-wallet', [RechargeWalletController::class, 'rechargeWallet'])
	->middleware('auth:api');