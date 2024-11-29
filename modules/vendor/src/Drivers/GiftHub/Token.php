<?php

namespace App\Vendor\Drivers\GiftHub;

use Illuminate\Support\Facades\Cache;

trait Token
{
    /**
     * Get the new token
     *
     * @return array
     */
	public function refreshToken()
	{
        $data = [];
        if(Cache::store('redis')->has('gifthub-vendor-extra-data')) {
            $data = Cache::store('redis')->get('gifthub-vendor-extra-data');
        } else {
            $vendor = \DB::table('vendors')
                ->select(['service_name', 'extra_data'])
                ->where('service_name', 'gifthub')
                ->first();

                if($vendor->extra_data) {
                    $extra = json_decode($vendor->extra_data, true);
                    Cache::store('redis')->put('gifthub-vendor-extra-data', $extra, now()->addDays(365));
                    $data = $extra;
                }
        }


        if(count($data) && isset($data['clientId']) && isset($data['clientSecret'])) {
            $data = array(
                'client-id: ' . decrypt($data['clientId']),
                'client-secret: ' . decrypt($data['clientSecret'])
            );
        }

		return $this->send('GET', "auth/jwt", '', $data, true);
	}
}