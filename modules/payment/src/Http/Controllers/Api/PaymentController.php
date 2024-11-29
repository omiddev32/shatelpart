<?php

namespace App\Payment\Http\Controllers\Api;

use App\Core\Controller;
use Illuminate\Http\Request;
use App\Payment\Gateway;
use App\Payment\Drivers\Jibit;
use App\User\Entities\User;
use App\Order\Entities\Order as OrderEntity;
use App\Payment\Entities\Transaction;
use Morilog\Jalali\Jalalian;
use Illuminate\Support\Facades\Validator;
use App\Order\Enums\OrderStatusEnum;
use DB;

class PaymentController extends Controller
{
    public function payment(Request $request)
    {
        $result = app('payment-gateway')->purchase('novin', 10000, 1);
        dd($result);
    }


    /**
     * Verify Request
     * Called from the payment gateway
     *
     * @route '/api/payments/verify-payment'
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyRequest(Request $request)
    {
        if (empty($request['amount']) || empty($request['purchaseId']) || empty($request['status'])) {
            return redirect(env('FRONT_URL'));
        }

        //get data from query string
        $amount = $request['amount'];
        $refNum = $request['purchaseId'];

        $transaction = Transaction::where('tracking_code', $refNum)->first();

        if (! $transaction) {
            return redirect(env('FRONT_URL'));
        } 

        $user = User::find($transaction->user_id);
        $gateway = new Gateway(
            new Jibit(config('jibit.api_key'), config('jibit.api_secret'))
        );
        if($request['status'] === 'SUCCESSFUL') {


            $verifyRequest = $gateway->paymentVerify($refNum);
            if (!empty($verifyRequest['status']) && $verifyRequest['status'] === 'SUCCESSFUL') {

                $order = $gateway->getOrderById($refNum);
                $payTime = now();
                $orderId = null;

                if(isset($order['numberOfElements']) && $order['numberOfElements'] > 0) {

                    if($order['elements'][0]['additionalData']['type'] == 'walletRecharge') {
                        $transaction->update([
                            'ip' => $request['payerIp'],
                            'extra' => json_encode($order['elements'][0]['additionalData'], true),
                            'reternable' => true,
                            'gateway_data' => json_encode($order['elements'][0] , true),
                            'paid_at' => $payTime,
                            'status' => 'success'
                        ]);

                        $user->wallet_balance = $user->wallet_balance + ($amount);
                        $user->withdrawable_credit = $user->withdrawable_credit + ($amount);
                        $user->update();
                    } else if($order['elements'][0]['additionalData']['type'] == 'PaymentOfPartOfTheOrderCost' || $order['elements'][0]['additionalData']['type'] == 'PayForTheProduct') {

                        $entity = OrderEntity::find($transaction->order_id);
                        $orderId = $entity->id;

                        $transaction->update([
                            'order_id' => null,
                            'ip' => $request['payerIp'],
                            'gateway_data' => json_encode($order['elements'][0] , true),
                            'paid_at' => $payTime,
                            'status' => 'success'
                        ]);

                        User::where('id', $user->id)->update([
                            'wallet_balance' => $user->wallet_balance + ($transaction->amount),
                            'withdrawable_credit' => $user->withdrawable_credit + ($transaction->amount)
                        ]);

                        $refrenceNumber = rand(123456, 999999). time();

                        Transaction::create([
                            'user_id' => $user->id,
                            'order_id' => $entity->id,
                            'reference_number' => $refrenceNumber,
                            'tracking_code' => $refrenceNumber,
                            'amount' => $entity->product_price + $entity->tax_price,
                            'type' => 'Order',
                            'description' => "پرداخت شده برای محصول {$entity->product_id}",
                            'reternable' => true,
                            'mode' => 'Decrement',
                            'email'=> $user->email ?: '',
                            'mobile'=> $user->phone_number ?: '',
                            'paid_at' => $payTime,
                            'extra' => json_encode([
                                'type' => $order['elements'][0]['additionalData']['type'],
                                'orderId' => $entity->id,
                                'purchaseId' => $refrenceNumber
                            ], true),                            
                            'status' => 'success',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        $entity->update([
                            'status' => OrderStatusEnum::PROCESSING,
                            'price_paid' => $entity->product_price + $entity->tax_price,
                            'paid_at' => $payTime
                        ]);

                        $user = $user->fresh();

                        User::where('id', $user->id)->update([
                            'purchase_amount' => $user->purchase_amount + ($entity->product_price + $entity->tax_price),
                            'wallet_balance' => $user->wallet_balance - ($entity->product_price + $entity->tax_price),
                            'withdrawable_credit' => $user->withdrawable_credit > ($entity->product_price + $entity->tax_price) ? $user->withdrawable_credit - ($entity->product_price + $entity->tax_price) : 0
                        ]);

                        \App\Order\Jobs\Cysend\MakeOrderCysendJob::dispatch($entity);
                    }
                }

                if($orderId) {
                    return redirect(env('FRONT_URL') . "/transaction?referenceNumber={$transaction->reference_number}&orderId={$orderId}");
                }

                return redirect(env('FRONT_URL') . "/transaction?referenceNumber={$transaction->reference_number}");
            }

            $transaction->update([
                'ip' => $request['payerIp'],
                'reternable' => false,
                'gateway_data' => json_encode($request->all() , true),
                'status' => 'failed' 
            ]);

            return redirect(env('FRONT_URL') . "/transaction?referenceNumber={$transaction->reference_number}");
        }

        $transaction->update([
            'ip' => $request['payerIp'],
            'reternable' => false,
            'gateway_data' => json_encode($request->all() , true),
            'status' => $request['failReason'] == 'CANCELLED_BY_USER' ? 'cancel' : 'failed' 
        ]);

        return redirect(env('FRONT_URL') . "/transaction?referenceNumber={$transaction->reference_number}");

    }    

    /**
     * Payment result
     *
     * @route '/api/payments/result'
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function paymentResult(Request $request)
    {        
        $validator = Validator::make($request->all(), [
            'referenceNumber' => "required"
        ]);

        if($validator->fails()) :
            return response()->json([
                'errors'=> $validator->errors()
            ] , 422);
        endif;

        $user = auth()->user();

        $transaction = Transaction::where(['reference_number' => $request->referenceNumber, 'user_id' => $user->id])->where('status', '!=', 'init')->where('admin_id', null)->first();

        if (! $transaction) {
            return json_response([
                'error' => __("Transaction Not Found!")
            ], 404);
        }

        $extra = json_decode($transaction->extra, true);

        return json_response([
            'type' => $transaction->status,
            'amount' => number_format($transaction->amount / 10),
            'referenceNumber' => $transaction->reference_number,
            'trackingCode' => $transaction->tracking_code,
            'time' => Jalalian::forge($transaction->status == 'success' ? $transaction->paid_at : $transaction->updated_at)->format("Y-m-d H:i:s"),
            'redirect' => isset($extra['type']) ? $extra['type'] : 'ordersPage'
        ], 200);

    }
}
