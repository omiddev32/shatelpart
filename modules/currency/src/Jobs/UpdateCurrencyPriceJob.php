<?php

namespace App\Currency\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\{SerializesModels , InteractsWithQueue};
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Currency\Services\UpdatePriceService;
use App\Order\Entities\OrderSetting;
use App\Currency\Entities\Currency;
use Carbon\Carbon;

class UpdateCurrencyPriceJob implements ShouldQueue
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
        $setting = \DB::table('order_settings')->select(['currency_api_driver', 'currency_api_duration', 'calling_time_currency_price_api'])->first();

        if($setting) {
            if($setting->calling_time_currency_price_api) {
                $durationTime = Carbon::parse($setting->calling_time_currency_price_api)
                    ->addminutes($setting->currency_api_duration)
                    ->format('Y-m-d H:i') . ':00';
                if(now()->format('Y-m-d H:i:s') >= $durationTime) {
                    $this->updateData($setting);
                }
            } else {
                $this->updateData($setting);
            }
        }
    }

    protected function updateData($setting)
    {
        $driver = $setting ? $setting->currency_api_driver : 'NAVASAN';
        $service = new UpdatePriceService($driver);
        $apiService = $service->getPrices();

        $updateList = [];

        if($apiService) {
            $currencies = Currency::where("meta->{$driver}", '!=', '')->get()->pluck('meta', 'id')->toArray();
            foreach($currencies as $id => $currency) {
                $meta = json_decode($currency);
                if($apiService && isset($apiService[$meta->{$driver}])) {
                    $updateList[] = [
                        'id' => $id,
                        'last_price' => +$apiService[$meta->{$driver}]['value'],
                        'driver_last_price_update' => $driver,
                        'last_price_update' => now(),
                    ];
                        
                }
            }
        }

        if(count($updateList)) {
            Currency::batchUpdate($updateList, 'id');

            \DB::table('order_settings')->update([
                'calling_time_currency_price_api' => now()
            ]);   
        }

    }
}
