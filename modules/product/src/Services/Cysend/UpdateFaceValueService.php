<?php

namespace App\Product\Services\Cysend;

use App\Product\Entities\FaceValueApi;
use Log;

class UpdateFaceValueService
{
	public function upsertFixedData($data)
	{
		try {
	        $bulk = FaceValueApi::query()->bulk();
	        $bulk->uniqueBy(['face_value_id'])
	            ->upsert(
	                collect($data)->map(fn($faceValue): array => [
	                    'vendor_id' => 2,
	                    'product_id' => "cysend-{$faceValue->product_id}",
	                    'face_value_id' => "cysend-{$faceValue->face_value_id}",
	                    'type' => 'fixed',
	                    'face_value_currency' => $faceValue->face_value_currency,
	                    'face_value' => $faceValue->face_value,
	                    'definition' => $faceValue->definition,
	                    'cost_currency' => $faceValue->cost_currency,
	                    'cost' => $faceValue->cost,
	                    'promotion' => $faceValue->promotion,
	                    'created_at' => now(),
	                    'updated_at' => now(),
	                ])->toArray()
	            );
	       return true;
		} catch (\Exception $e) {
			Log::info("**--**--**--**--**--**");
			Log::info("\nLocation: \App\Product\Services\Cysend\UpdateFaceValueService::upsertFixedData()");
			Log::info("**--**--**--**--**--**");
			return false;
		}
	}	

	public function upsertRangeData($data)
	{
		try {
	        $bulk = FaceValueApi::query()->bulk();
	        $bulk->uniqueBy(['face_value_id'])
	            ->upsert(
	                collect($data)->map(fn($faceValue): array => [
                        'vendor_id' => 2,
                        'product_id' => "cysend-{$faceValue->product_id}",
                        'face_value_id' => "cysend-{$faceValue->face_value_id}",
                        'type' => 'range',
                        'face_value_currency' => $faceValue->face_value_currency,
                        'face_value' => $faceValue->face_value_from,
                        'max_face_value' => $faceValue->face_value_to,
                        'face_value_step' => $faceValue->face_value_step,
                        'cost_currency' => $faceValue->cost_currency,
                        'cost' => $faceValue->minimum_cost,
                        'max_cost' => $faceValue->maximum_cost,
                        'promotion' => $faceValue->promotion,
                        'created_at' => now(),
                        'updated_at' => now(),
	                ])->toArray()
	            );
	      return true;
		} catch (\Exception $e) {
			Log::info("**--**--**--**--**--**");
			Log::info("\nLocation: \App\Product\Services\Cysend\UpdateFaceValueService::upsertRangeData()");
			Log::info("**--**--**--**--**--**");
			return false;
		}
	}
}
