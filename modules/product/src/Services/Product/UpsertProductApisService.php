<?php

namespace App\Product\Services\Product;

use App\Product\Entities\ProductApi;
use Log;

class UpsertProductApisService
{
	public function upsertProductsData($data)
	{
		if(count($data)) {
			try {

				ProductApi::upsert($data, ['product_id']);

		        // $bulk = ProductApi::query()->bulk();
		        // $bulk->uniqueBy(['product_id'])
		        //     ->upsert($data);


		           return true;
			} catch (\Exception $e) {
				Log::info("**--**--**--**--**--**");
				Log::info("**--**--**--**--**--**");
				Log::info("\n\nLocation: \App\Product\Services\Product\UpsertProductApisService::upsertProductsData()");
				Log::info("Problem in upsert - product api - send\n\n");
				Log::info($e);
				Log::info("**--**--**--**--**--**");
				Log::info("**--**--**--**--**--**");
			}
		}
	}
}
