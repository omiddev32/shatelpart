<?php

namespace App\Product\Http\Controllers\Api;

use App\Core\Controller;
use Illuminate\Http\Request;
use App\Product\Entities\ProductVariant;
use Illuminate\Support\Facades\Validator;
use App\Currency\Entities\{Currency, FormulaGroup};

class ProductVariantController extends Controller
{
    /**
     * Handles Product variant Request
     *
     * @route '/api/variant/{variantId}'
     * @param Request $request
     * @param integer $variantId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductVariant(Request $request, $variantId)
    {
        $user = auth()->guard('api')->user();
        $lang = app()->getLocale();

        $variant = ProductVariant::find($variantId);

        if(! $variant) {
            return json_response([
                'error' => __("The desired variant does not exist!")
            ], 404);
        } else if($variant->type !== 'range') {
            return json_response([
                'error' => __("Not Access!")
            ], 403);    
        }

        $validator = Validator::make($request->all(), [
            'amount' => "required|numeric|between:{$variant->face_value},{$variant->max_face_value}"
        ]);

        if($validator->fails()) :
            return response()->json([
                'errors'=> $validator->errors()
            ] , 422);
        endif;

        $simulateCost = $request->amount * ($variant->cost / $variant->face_value);

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
                ->where('start_range', '<=', $simulateCost)
                ->where('end_range', '>=', $simulateCost)
                ->first();
        }

        $calc = new \App\Currency\Services\CalculatePriceService;

        return json_response([
            'cost' => number_format($calc->calculation($formulaItem->formula, $simulateCost, $rate, $formulaGroup->et, $formulaGroup->un, $formulaGroup->pr)),
        ], 200);
    }

}
