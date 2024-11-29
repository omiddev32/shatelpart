<?php

namespace App\Product\Services\Variant;

use App\Product\Entities\Product;

class UpdateProductPriceService
{
	public static function updatePrice($productId)
	{
        $product = Product::with(['variants' => function($query) {
            $query->where('vendor_id', 2);
        }])->where('id', $productId)->first();

        if($count = $product->variants->count()) {
            $firstVariant = $product->variants->first();
            if($count == 1 && $firstVariant->type === 'fixed') {
                $product->update([
                    'price_type' => 'single',
                    'currency_price' => $firstVariant->face_value_currency,
                    'min_price' => $firstVariant->face_value,
                    'max_price' => $firstVariant->face_value,
                    'cost_currency' => $firstVariant->cost_currency,
                    'min_cost' => $firstVariant->cost,
                    'max_cost' => $firstVariant->cost,
                ]);
            } else if($count == 1 && $firstVariant->type === 'range') {
                $product->update([
                    'price_type' => 'range',
                    'currency_price' => $firstVariant->face_value_currency,
                    'min_price' => $firstVariant->face_value,
                    'max_price' => $firstVariant->max_face_value,
                    'cost_currency' => $firstVariant->cost_currency,
                    'min_cost' => $firstVariant->cost,
                    'max_cost' => $firstVariant->max_cost,
                ]);
            } else if($count > 1 && $firstVariant->type === 'fixed') {
                $product->update([
                    'price_type' => 'fixed',
                    'currency_price' => $firstVariant->face_value_currency,
                    'min_price' => $product->variants->min('face_value'),
                    'max_price' => $product->variants->max('face_value'),
                    'cost_currency' => $firstVariant->cost_currency,
                    'min_cost' => $product->variants->min('cost'),
                    'max_cost' => $product->variants->max('cost'),
                ]);
            }
        }
	}
}