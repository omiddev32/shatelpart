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



        // $gateway = new Gateway(
        //     new Jibit(config('jibit.api_key'), config('jibit.api_secret'))
        // );


        try {

            $refrenceNumber = rand(123456, 999999). time();
            $price = $request->price;
            $driver = config('paymentGateway.driver');
            $gateway = app('payment-gateway')->purchase(config('paymentGateway.driver'), $price, $user->id);
                

            $res = json_decode($gateway, true);

            $driverName = 'Jibit';

            if(is_array($res) && isset($res['transactionId'])) {

                // $res = $gateway->paymentRequest($price, $refrenceNumber, $user->id, config('jibit.callback_url'), __("Wallet Recharge"), [
                //     'type' => 'walletRecharge'
                // ]);

                // if($res['status'] === 'Success') {
                    DB::table('transactions')->insert([
                        'user_id' => $user->id,
                        'amount' => $price,
                        'reternable' => true,
                        'mode' => 'Increment',
                        'gateway' => $driverName,
                        'reference_number' => $refrenceNumber,
                        'tracking_code' => $res['transactionId'],
                        'email'=> $user->email ?: '',
                        'mobile'=> $user->phone_number ?: '',
                        'description' => __("Wallet Recharge"),
                        'extra' => json_encode([
                            'type' => 'walletRecharge',
                            'purchaseId' => $res['transactionId']
                        ], true),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    return json_response([
                        'redirect' => $redirectUrl,
                    ], 200);
                // }
            } else {
                throw new \Exception('There is a problem with the payment gateway', 500);
            }


            if($driver == 'novin') {
                $driverName = 'Novin';
                $redirectUrl = url("/api/payments/redirect-to-bank/" . $res['transactionId']);
            } else {
                $redirectUrl = $res['redirect'];
            }

        } catch (\Exception $e) {
            return json_response([
                'message' => __("There is a problem with the payment gateway")
            ], 402);
        }

    }
}
