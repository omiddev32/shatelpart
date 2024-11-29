<?php

namespace App\Wallet\Http\Controllers\Api;

use App\Core\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Payment\Facades\PaymentGateway;
use App\Payment\Gateway;
use App\Payment\Drivers\Jibit;
use DB;

class RechargeWalletController extends Controller
{
    public function rechargeWallet(Request $request)
    {
        $user = auth()->user();

        if(! $user->register_datetime) {
            return json_response([
                'message' => __("Dont Access!"),
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'price' => "required|numeric|min:10000"
        ]);

        if($validator->fails()) :
            return response()->json([
                'errors'=> $validator->errors()
            ] , 422);
        endif;

        $gateway = new Gateway(
            new Jibit(config('jibit.api_key'), config('jibit.api_secret'))
        );

        $refrenceNumber = rand(123456, 999999). time();
        $price = $request->price;

        $res = $gateway->paymentRequest($price, $refrenceNumber, $user->id, config('jibit.callback_url'), __("Wallet Recharge"), [
            'type' => 'walletRecharge'
        ]);

        if($res['status'] === 'Success') {
            DB::table('transactions')->insert([
                'user_id' => $user->id,
                'amount' => $price,
                'reternable' => true,
                'mode' => 'Increment',
                'gateway' => 'Jibit',
                'reference_number' => $refrenceNumber,
                'tracking_code' => $res['purchaseId'],
                'email'=> $user->email ?: '',
                'mobile'=> $user->phone_number ?: '',
                'description' => __("Wallet Recharge"),
                'extra' => json_encode([
                    'type' => 'walletRecharge',
                    'purchaseId' => $res['purchaseId']
                ], true),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return json_response([
                'redirect' => $res['redirect'],
            ], 200);
        }

        return json_response([
            'message' => __("There is a problem with the payment gateway")
        ], 402);
    }
}