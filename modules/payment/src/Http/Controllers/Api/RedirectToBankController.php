<?php

namespace App\Payment\Http\Controllers\Api;

use App\Core\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RedirectToBankController extends Controller
{
    public function redirect(Request $request, $transactionId)
    {
        if(Cache::store('redis')->has("payment-transaction-{$transactionId}")) {
            return view("payment::redirectPayment")->with(Cache::store('redis')->get("payment-transaction-{$transactionId}"));
        }

        return abort(403);
    }
}
