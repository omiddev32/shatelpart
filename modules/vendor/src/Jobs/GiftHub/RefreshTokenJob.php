<?php

namespace App\Vendor\Jobs\GiftHub;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\{SerializesModels , InteractsWithQueue};
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
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
        \Log::info('Call GiftHub Refresh Token');
        $service = new \App\Vendor\Drivers\GiftHub\Api;
        $result = $service->refreshToken();
        if($result) {
            $token = encrypt($result);
            \DB::table('vendors')->where('service_name', 'gifthub')->update([
                'token' => $token
            ]);
            Cache::store('redis')->forget('gifthub-vendor-token');
            Cache::store('redis')->put('gifthub-vendor-token', $token, now()->addDays(365));
        }
    }
}
