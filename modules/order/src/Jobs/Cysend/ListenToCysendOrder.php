<?php

namespace App\Order\Jobs\Cysend;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\{SerializesModels , InteractsWithQueue};
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Vendor\Services\VendorService;
Use App\Order\Entities\Order;
use DB;

class ListenToCysendOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Order id
     *
     * @var int
     */
    private $orderId;

    /**
     * Order Number
     *
     * @var string
     */
    private string $orderNumber;

    /**
     * User uId
     *
     * @var string
     */
    private string $userUId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($orderId, string $orderNumber, string $userUId)
    {
        $this->orderId = $orderId;
        $this->orderNumber = $orderNumber;
        $this->userUId = $userUId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $service = new VendorService();
        $responseData = $service->retrieveOrder($this->orderNumber, $this->userUId);

        if(is_array($responseData) && isset($responseData[0])) {
            if($responseData[0]->response_code == '09') {
                ListenToCysendOrder::dispatch($this->orderId, $this->orderNumber, $this->userUId)
                    ->delay(now()->addminutes(3));
            } else if($responseData[0]->response_code == '00'){
                SuccessCysendOrder::dispatch($this->orderId, $responseData);
            } else if($responseData[0]->response_code == '06'){
                \App\Order\Jobs\FailedOrder::dispatch($this->orderId);
            } else {
                \Log::info('Error 0 In ListenToCysendOrder');
                \Log::info($responseData);
            }
        }
    }
}
