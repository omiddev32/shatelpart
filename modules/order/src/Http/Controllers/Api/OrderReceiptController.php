<?php

namespace App\Order\Http\Controllers\Api;

use App\Core\Controller;
use Illuminate\Http\Request;
use App\Product\Entities\Product;
use Illuminate\Support\Facades\Validator;
use App\Currency\Entities\{Currency, FormulaGroup};
use App\Vendor\Services\VendorService;
use Storage;
use DB;

class OrderReceiptController extends Controller
{
    /**
     * Receive order receipt
     * 
     * @route '/api/orders/order-receipt/{orderId}'
     * @param Request $request
     * @param $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function orderReceipt(Request $request, $orderId)
    {
        $user = auth()->user();
        $lang = app()->getLocale();
        $order = DB::table('orders')->where('id', $orderId)->where('user_id', $user->id)->first();

        if(! $order || $order->paid_at != null) {
            return json_response([
                'message' => __("Order not found!"),
            ], 404);
        }

        $product = Product::with(['variants' => function($query) use($order) {
            $query->where('id', $order->variant_id);
        }, 'categories:id,title,status'])->find($order->product_id);

        $orderSetting = DB::table('order_settings')->first();

        $price = 0;
        $cost = 0;


        $beneficiaryList = [];
        $beneficiaryInformation = [];

        foreach($product->beneficiary_information as $beneficiary) {
            $beneficiaryList[$beneficiary['fields']['name']] = [
                'display_name' => $beneficiary['fields']['display_name'][$lang],
                'type' => $beneficiary['fields']['type'],
                'description' => $beneficiary['fields']['description'] ?: '',
                'required' => $beneficiary['fields']['required'],
                'pattern' => $beneficiary['fields']['pattern'],
            ];
        }

        $orderBeneficiaryInformation = [];

        if($product->beneficiary_information != null) {

            $orderBeneficiaryInformation = json_decode($order->beneficiary_information, true);

            foreach($orderBeneficiaryInformation as $item) {
                $beneficiaryInformation[] = [
                    'display_name' => isset($beneficiaryList[$item['name']]) ? $beneficiaryList[$item['name']]['display_name'] : $item['name'],
                    'type' => isset($beneficiaryList[$item['name']]) ? $beneficiaryList[$item['name']]['type'] : 'input',
                    'description' => isset($beneficiaryList[$item['name']]) ? $beneficiaryList[$item['name']]['description'] : '',
                    'required' => isset($beneficiaryList[$item['name']]) ? $beneficiaryList[$item['name']]['required'] : true,
                    'pattern' => isset($beneficiaryList[$item['name']]) ? $beneficiaryList[$item['name']]['pattern'] : '',
                    'name' => $item['name'],
                    'value' => $item['value'],
                ];
            }
        }



        if($product->variants->count()) {

            $variant = $product->variants[0];
            $service = new VendorService();
            $apiResponse = $service->getCost($variant, $orderBeneficiaryInformation, ($variant->type == 'range' ? $order->variant_value : $variant->face_value));


            if(! is_array($apiResponse) && property_exists($apiResponse, 'code')) {
                return json_response([
                    'error' => __("Internal Error")
                ], 521);
            }

            $cost = $apiResponse[0]->cost;



            // if($variant->type != 'range') {
            //     $cost = $variant->cost;
            // } else {
            //     $cost = $order->variant_value * ($variant->cost / $variant->face_value);
            // }
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
                    ->where('start_range', '<=', $cost)
                    ->where('end_range', '>=', $cost)
                    ->first();
            }
            $calc = new \App\Currency\Services\CalculatePriceService;
            $price = $calc->calculation($formulaItem->formula, $cost, $rate, $formulaGroup->et, $formulaGroup->un, $formulaGroup->pr);
        } else {

            return json_response([
                'error' => __("Internal Error")
            ], 521);
            
        }

        $tax = 0;

        if($orderSetting && $orderSetting->vat_status && $price > 0) {
            $tax = ($price * $orderSetting->vat_rate) / 100;
        }

        $categories = $product->categories->where('status', true)->map(fn($category): array => [
                    'id'=> $category->id,
                    'title' => $category->title
                ])->toArray();


        return json_response([
            'order' => [
                'productName' => $product->display_name ?: $product->name,
                'productImage' => $product->image ? Storage::disk('products')->url($product->image) : '',
                'beneficiaryInformation' => $beneficiaryInformation,
                'price' => $price,
                'tax' => $tax,
                'categories' => $categories,
                'wallet_balance' => ($user->wallet_balance > 0 ? number_format($user->wallet_balance / 10) : 0)
            ]
        ], 200);

    }

    /**
     * Update order receipt
     * 
     * @route '/api/orders/updte-order-receipt/{orderId}'
     * @param Request $request
     * @param $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrderReceipt(Request $request, $orderId)
    {
        $user = auth()->user();
        $order = DB::table('orders')->where('id', $orderId)->where('user_id', $user->id)->first();

        if(! $order || $order->paid_at != null) {
            return json_response([
                'message' => __("Order not found!"),
            ], 404);
        }

        $product = Product::find($order->product_id);

        if($product->beneficiary_information == null || $product->beneficiary_information == []) {
            return json_response([
                'message' => __("The receipt cannot be edited!"),
            ], 402);
        }

        $validator = Validator::make($request->all(), [
            'beneficiary_information' => $product && $product->beneficiary_information != null ? 'required|array': 'nullable'
        ]);

        if($validator->fails()) :
            return response()->json([
                'errors'=> $validator->errors()
            ] , 422);
        endif;

        $beneficiaryInformation = $request->beneficiary_information ?: [];


        DB::table('orders')->where('id', $orderId)->update([
            'beneficiary_information' => json_encode($beneficiaryInformation, true),
        ]);

        return json_response([
            'message' => __("Receipt edited.")
        ], 200);
    }
}
