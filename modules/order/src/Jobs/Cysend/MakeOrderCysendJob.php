<?php

namespace App\Order\Jobs\Cysend;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\{SerializesModels , InteractsWithQueue};
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Vendor\Services\VendorService;
Use App\Order\Entities\Order;
use DB;

class MakeOrderCysendJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Order Model
     *
     * @var Class|Entity
     */
    private $order;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $variant = DB::table('product_variants')->find($this->order->variant_id);
        $beneficiaryInformation = [];
        if($this->order->beneficiary_information != null) {
            $beneficiaryInformation = json_decode($this->order->beneficiary_information, true);
        }

        $service = new VendorService();
        $response = $service->placeOrder($this->order->id, $variant, $beneficiaryInformation, config('cysend.mode'), $this->order->variant_value);

        if(is_array($response) && isset($response[0])) {
            $res = $response[0];
            if($res->response_code == '00') {
                SuccessCysendOrder::dispatch($this->order->id, $response[0]);
            } else {
                $responseData = $service->retrieveOrder($res->uid, $res->user_uid);

                if(is_array($responseData) && isset($responseData[0])) {
                    if($responseData[0]->response_code == '09') {
                        Order::where('id', $this->order->id)->update(['status' => 'delayed']);
                        ListenToCysendOrder::dispatch($this->order->id, $res->uid, $res->user_uid)
                            ->delay(now()->addminutes(3));
                    } else if($responseData[0]->response_code == '00'){
                        SuccessCysendOrder::dispatch($this->order->id, $responseData);
                    } else if($responseData[0]->response_code == '06'){
                        \App\Order\Jobs\FailedOrder::dispatch($this->order->id);
                    } else {
                        \Log::info('Error 0 In MakeOrderCysendJob');
                        \Log::info($responseData);
                    }
                } else {
                    \Log::info('Error 1 In MakeOrderCysendJob');
                    \Log::info($responseData);
                }
            }
        } else {
            /* Error Handleing */
            \App\Order\Jobs\FailedOrder::dispatch($this->order->id);
        }
    }
}
