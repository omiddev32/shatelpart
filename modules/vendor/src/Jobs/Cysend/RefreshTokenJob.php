<?php

namespace App\Vendor\Jobs\Cysend;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\{SerializesModels , InteractsWithQueue};
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Vendor\Services\VendorService;
use Illuminate\Support\Facades\Cache;

class RefreshTokenJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Log::info('Call Cysend Refresh Token');
        $service = new \App\Vendor\Drivers\Cysend\Api;
        $result = $service->refreshToken();
        if($result && property_exists($result, 'token')) {
            \DB::table('vendors')->where('service_name', 'cysend')->update([
                'token' => $result->token
            ]);
            Cache::store('redis')->forget('cysend-vendor-token');
            Cache::store('redis')->put('cysend-vendor-token', $vendor->token, now()->addDays(365));
        }
    }
}
