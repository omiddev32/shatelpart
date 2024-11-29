<?php

namespace App\Order\Http\Controllers\Api;

use App\Core\Controller;
use Illuminate\Http\Request;
use App\Product\Entities\Product;
use Illuminate\Support\Facades\Validator;
use App\Currency\Entities\{Currency, FormulaGroup};
use App\Vendor\Services\VendorService;
use DB;

class AddToOrderController extends Controller
{
    /**
     * Add product to order
     *
     * @route '/api/orders/data-for-payment'
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addToOrder(Request $request)
    {
        $product = Product::where(['status' => true, 'id' => $request->product_id, 'maintenance' => false])->first();

        $variant = DB::table('product_variants')
            ->where('product_id', $request->product_id)
            ->where('id', $request->variant_id)
            ->first() ?: null;

        $variantValueRules = [];

        if($variant && $variant->type == 'range') {
            $variantValueRules = ['required', new \App\Order\Rules\VariantValueRule($variant)];
        } else {
            $variantValueRules = [new \App\Order\Rules\VariantValueRule($variant)];
        }

        if($request->beneficiary_information) {
            $request->merge(['beneficiary_information' => json_decode($request->beneficiary_information, true)]);
        }

        $validator = Validator::make($request->all(), [
            'product_id' => ['required', new \App\Order\Rules\ProductRule($product)],
            'variant_id' => ['required', new \App\Order\Rules\VariantRule($variant)],
            'variant_value' => $variantValueRules,
            'beneficiary_information' => $product && $product->beneficiary_information != null ? 'required|array': 'nullable'
        ]);

        if($validator->fails()) :
            return response()->json([
                'errors'=> $validator->errors()
            ] , 422);
        endif;

        $orderNumber = rand(123456, 999999) . '' . time();
        $beneficiaryInformation = $request->beneficiary_information ?: [];

        $orderSetting = DB::table('order_settings')->first();


        $service = new VendorService();
        $apiResponse = $service->getCost(
            $variant,
            $beneficiaryInformation,
            ($variant->type == 'range' ? $request->variant_value : $variant->face_value)
        );

        if(! is_array($apiResponse) && property_exists($apiResponse, 'code')) {
            return json_response([
                'error' => __("Internal Error")
            ], 521);
        }

        $cost = $apiResponse[0]->cost;


        // if($variant->type != 'range') {
        //     $cost = $variant->cost;
        // } else {
        //     $cost = $request->variant_value * ($variant->cost / $variant->face_value);
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

        $price = $price * 10;

        $tax = 0;

        if($orderSetting && $orderSetting->vat_status && $price > 0) {
            $tax = ($price * $orderSetting->vat_rate) / 100;
        }

        $orderId = DB::table('orders')->insertGetId([
            'user_id' => auth()->user()->id,
            'vendor_id' => $variant->vendor_id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'order_number' => $this->generateOrderNumber(),
            'reference_number' => $orderNumber,
            'product_price' => $price,
            'tax_price' => $tax,
            'variant_value' => ($variant->type == 'range' ? $request->variant_value : $variant->face_value),
            'beneficiary_information' => json_encode($beneficiaryInformation, true),
            'expires_at' => now()->addminutes($orderSetting->order_validity_period),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return json_response([
            'message' => __("The order was successfully placed."),
            'messageCode' => 'Success',
            'orderId' => $orderId,
            'referenceNumber' => $orderNumber,
        ], 200);
    }

    private function generateOrderNumber()
    {
        $code = rand(123456, 999999);

        if(DB::table('orders')->where('order_number', $code)->exists()) {
            $code = $this->generateOrderNumber();
        }

        return $code;
    }
}
