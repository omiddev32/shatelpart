<?php

namespace App\Product\Services\Cysend;

use App\Product\Entities\ProductApi;
use Log;

class UpsertProductApisService
{
	public function upsertProductsData($data)
	{
		try {
	        $bulk = ProductApi::query()->bulk();
	        $bulk->uniqueBy(['product_id'])
	            ->upsert($data);
	           return true;
		} catch (\Exception $e) {
			Log::info("**--**--**--**--**--**");
			Log::info("**--**--**--**--**--**");
			Log::info("\n\nLocation: \App\Product\Services\Cysend\UpsertProductApisService::upsertProductsData()");
			Log::info("Problem in upsert - product api - send\n\n");
			Log::info($e);
			Log::info("**--**--**--**--**--**");
			Log::info("**--**--**--**--**--**");
		}
	}
}
