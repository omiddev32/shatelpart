<?php

namespace App\Order\Http\Controllers\Api;

use App\Core\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Order\Entities\Order;
use App\User\Entities\User;
use App\Currency\Entities\{Currency, FormulaGroup};
use App\Vendor\Services\VendorService;
use App\Payment\Gateway;
use App\Payment\Drivers\Jibit;
use App\Order\Enums\OrderStatusEnum;
use DB;

class OrderPaymentController extends Controller
{
    /**
     * Order Payment
     * 
     * @route '/api/orders/order-payment'
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function orderPayment(Request $request)
    {
        $user = auth()->user();
        $lang = app()->getLocale();

        $validator = Validator::make($request->all(), [
            'orderId' => ['required', 'exists:orders,id'],
            'payWith' => ["required",'in:wallet,onlineGateway,combineMethod']
        ]);

        if($validator->fails()) :
            return response()->json([
                'errors'=> $validator->errors()
            ] , 422);
        endif;

        $order = Order::where('id', $request->orderId)->where('user_id', $user->id)->first();

        if(! $order || $order->paid_at != null) {
            return json_response([
                'message' => __("Order not found!"),
            ], 404);
        }

        $variant = DB::table('product_variants')->find($order->variant_id);

        $beneficiaryInformation = [];

        if($order->beneficiary_information != null) {
            $beneficiaryInformation = json_decode($order->beneficiary_information, true);
        }

        // $liveCost =

        $service = new VendorService();
        $apiResponse = $service->getCost($variant, $beneficiaryInformation, ($variant->type == 'range' ? $order->variant_value : $variant->face_value));

        if(! is_array($apiResponse) && property_exists($apiResponse, 'code')) {
            
            return json_response([
                'error' => __("Internal Error")
            ], 521);
        }
        $orderSetting = DB::table('order_settings')->first();

        $liveCost = $apiResponse[0]->cost;

        $currency = Currency::select('iso', 'currency_name', 'last_price')->where('iso', $variant->cost_currency)->first();
        $rate = 1;
        if($currency) {
            $rate = +($currency->last_price);
        }
        $costCurrency = $variant->cost_currency;
        $formulaGroup = FormulaGroup::with('formulas')->has('formulas')->whereHas('includeCurrencies', function($query) use($costCurrency) {
            $query->where('iso', $costCurrency);
        })->first();
        $formulaItem = null;
        if($formulaGroup) {
            $formulaItem = $formulaGroup->formulas
                ->where('start_range', '<=', $liveCost)
                ->where('end_range', '>=', $liveCost)
                ->first();
        }
        $calc = new \App\Currency\Services\CalculatePriceService;
        $price = $calc->calculation($formulaItem->formula, $liveCost, $rate, $formulaGroup->et, $formulaGroup->un, $formulaGroup->pr);

        $tax = 0;
        $rialPrice = $price * 10;

        if($orderSetting && $orderSetting->vat_status && $price > 0) {
            $tax = (($rialPrice * $orderSetting->vat_rate) / 100);
        }


        $paidAmount = 0;

        // if($order->transactions->count()) {
        //     foreach($order->transactions as $transaction) {
        //         $paidAmount += $transaction->amount;
        //     }
        // }

        $remains = ceil($rialPrice + $tax);

        if($request->payWith == 'wallet') {
            if($user->wallet_balance > $remains) {
                $ref = rand(123456, 999999). time();
                $this->paidWithWallet($user, $remains, $ref, $variant->id, $order);

                $order->update([
                    'status' => OrderStatusEnum::PROCESSING,
                    'product_price' => $rialPrice,
                    'tax_price' => $tax,
                    'price_paid' => $remains,
                    'paid_at' => now(),
                ]);

                \App\Order\Jobs\Cysend\MakeOrderCysendJob::dispatch($order);

                return json_response([
                    'status' => 'Success',
                    'mode' => 'Wallet',
                    'reference_number' => $ref,
                ], 200);

            }
            return json_response([
                'error' => __("Insufficient inventory")
            ], 408);
        } else if($request->payWith == 'combineMethod') {
            if($user->wallet_balance > $remains) {
                $ref = rand(123456, 999999). time();
                $this->paidWithWallet($user, $remains, $ref, $variant->id, $order);

                $order->update([
                    'status' => OrderStatusEnum::PROCESSING,
                    'product_price' => $rialPrice,
                    'tax_price' => $tax,
                    'price_paid' => $remains,
                    'paid_at' => now(),
                ]);

                \App\Order\Jobs\Cysend\MakeOrderCysendJob::dispatch($order);

                return json_response([
                    'status' => 'Success',
                    'mode' => 'Wallet',
                    'reference_number' => $ref,
                ], 200);
            }

            // $walletRef = rand(123456, 999999). time();
            // $this->paidWithWallet($user, $user->wallet_balance, $walletRef, $variant->id, $order);

            $order->update([
                'product_price' => $rialPrice,
                'tax_price' => $tax,
                'expires_at' => now()->addminutes($orderSetting->order_validity_period),
                // 'price_paid' => $user->wallet_balance,
            ]);

            $remains = $remains - $user->wallet_balance;

            $gateway = new Gateway(
                new Jibit(config('jibit.api_key'), config('jibit.api_secret'))
            );

            $refrenceNumber = rand(123456, 999999). time();

            $res = $gateway->paymentRequest($remains, $refrenceNumber, $user->id, config('jibit.callback_url'), __("Wallet Recharge"), [
                'type' => 'PaymentOfPartOfTheOrderCost',
                'orderId' => $order->id,
            ]);

            $this->paidOrder($user, $remains, $refrenceNumber, $res['purchaseId'], $variant->id, $order, "پرداخت شده برای محصول {$variant->id}", 'PaymentOfPartOfTheOrderCost');

            return json_response([
                'status' => 'Success',
                'mode' => 'Gateway',
                'redirect' => $res['redirect'],
            ], 200);
        }   

        $gateway = new Gateway(
            new Jibit(config('jibit.api_key'), config('jibit.api_secret'))
        );

        $refrenceNumber = rand(123456, 999999). time();

        $res = $gateway->paymentRequest($remains, $refrenceNumber, $user->id, config('jibit.callback_url'), __("Wallet Recharge"), [
            'type' => 'PayForTheProduct',
            'orderId' => $order->id,
        ]);

        $this->paidOrder($user, $remains, $refrenceNumber, $res['purchaseId'], $variant->id, $order, "پرداخت شده برای محصول {$variant->id}", 'PayForTheProduct');

        $order->update([
            'product_price' => $rialPrice,
            'tax_price' => $tax,
            'expires_at' => now()->addminutes($orderSetting->order_validity_period),
        ]);

        return json_response([
            'status' => 'Success',
            'mode' => 'Gateway',
            'redirect' => $res['redirect'],
        ], 200);
    }

    private function paidOrder($user, $remains, $ref, $tracking_code, $variantId, $order, $description, $type)
    {
        // User::where('id', $user->id)->update([
        //     'purchase_amount' => $user->purchase_amount + $remains,
        //     'wallet_balance' => $user->wallet_balance - $remains,
        //     'withdrawable_credit' => ($user->withdrawable_credit - $remains) > 0 ? $user->withdrawable_credit - $remains : 0
        // ]);

        $order->transaction()->create([
            'user_id' => $user->id,
            'reference_number' => $ref,
            'tracking_code' => $tracking_code,
            'amount' => $remains,
            'type' => 'BankDeposit',
            'description' => $description,
            'reternable' => true,
            'mode' => 'Increment',
            'gateway' => 'Jibit',
            'email'=> $user->email ?: '',
            'mobile'=> $user->phone_number ?: '',
            'extra' => json_encode([
                'type' => $type,
                'orderId' => $order->id,
                'purchaseId' => $tracking_code
            ], true),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function paidWithWallet($user, $remains, $ref, $variantId, $order)
    {
        User::where('id', $user->id)->update([
            'purchase_amount' => $user->purchase_amount + $remains,
            'wallet_balance' => $user->wallet_balance - $remains,
            'withdrawable_credit' => ($user->withdrawable_credit - $remains) > 0 ? $user->withdrawable_credit - $remains : 0
        ]);

        $order->transaction()->create([
            'user_id' => $user->id,
            'reference_number' => $ref,
            'tracking_code' => $ref,
            'amount' => $remains,
            'mode' => 'Decrement',
            'type' => 'Order',
            'reternable' => false,
            'status' => 'success',
            'description' => "پرداخت شده برای محصول {$variantId}",
            'paid_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

}
