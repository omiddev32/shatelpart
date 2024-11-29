<?php

namespace App\Product\Services\Cysend;

use App\Product\Entities\ProductVariant;
use Log;

class UpdateProductVariantService
{
	public function updateFixedData($data, array $providedProducts = [])
	{
		try {
	        $bulk = ProductVariant::query()->bulk();
	        $bulk->uniqueBy(['face_value_id'])
	            ->upsert(
	                collect($data)->whereIn('product_id', array_keys($providedProducts))->map(function($faceValue) use($providedProducts){
	                	return [
		        	        'vendor_id' => 2,
		                    'product_id' => $providedProducts[$faceValue->product_id],
		                	'face_value_id' => "cysend-{$faceValue->face_value_id}",
		                    'face_value_currency' => $faceValue->face_value_currency,
		                    'face_value' => $faceValue->face_value,
		                    'definition' => $faceValue->definition,
		                    'cost_currency' => $faceValue->cost_currency,
		                    'cost' => $faceValue->cost,
		                    'promotion' => $faceValue->promotion,
		                    'updated_at' => now(),
		                ];
	                })->toArray()
	            );
	           return true;
		} catch (\Exception $e) {
			Log::info("**--**--**--**--**--**");
			Log::info("**--**--**--**--**--**");
			Log::info("\n\nLocation: \App\Product\Services\Cysend\UpdateProductVariantService::updateFixedData()");
			Log::info("Problem in update - product variant - send\n\n");
			Log::info($e);
			Log::info("**--**--**--**--**--**");
			Log::info("**--**--**--**--**--**");
		}
	}	

	public function updateRangeData($data, array $providedProducts = [])
	{
		try {
	        $bulk = ProductVariant::query()->bulk();
	        $bulk->uniqueBy(['face_value_id'])
	            ->upsert(
	                collect($data)->whereIn('product_id', array_keys($providedProducts))->map(fn($faceValue): array => [
	                	'vendor_id' => 2,
	                    'product_id' => $providedProducts[$faceValue->product_id],
	                	'face_value_id' => "cysend-{$faceValue->face_value_id}",
                        'face_value_currency' => $faceValue->face_value_currency,
                        'face_value' => $faceValue->face_value_from,
                        'max_face_value' => $faceValue->face_value_to,
                        'face_value_step' => $faceValue->face_value_step,
                        'cost_currency' => $faceValue->cost_currency,
                        'cost' => $faceValue->minimum_cost,
                        'max_cost' => $faceValue->maximum_cost,
                        'promotion' => $faceValue->promotion,
                        'updated_at' => now(),
	                ])->toArray()
	            );
	           return true;
		} catch (\Exception $e) {
			Log::info("**--**--**--**--**--**");
			Log::info("**--**--**--**--**--**");
			Log::info("\n\nLocation: \App\Product\Services\Cysend\UpdateProductVariantService::updateRangeData()");
			Log::info("Problem in update - product variant - send\n\n");
			Log::info($e);
			Log::info("**--**--**--**--**--**");
			Log::info("**--**--**--**--**--**");
		}
	}
}
