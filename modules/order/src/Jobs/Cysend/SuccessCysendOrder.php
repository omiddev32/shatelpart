<?php

namespace App\Order\Jobs\Cysend;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\{SerializesModels , InteractsWithQueue};
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Message\Jobs\SendMessage;
use App\Order\Jobs\SendOrderDetailJob;
use App\Order\Entities\Order;

class SuccessCysendOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Order id
     *
     * @var int
     */
    private $orderId;

    /**
     * Response Data
     *
     * @var array
     */
    private $responseData;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($orderId, $responseData)
    {
        $this->orderId = $orderId;
        $this->responseData = $responseData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $order = Order::with(['product', 'user'])->find($this->orderId);
        $metaData = [];

        switch ($order->product->type) {
            case 'prepaid_code':
                $metaData = $this->prepaidCodeType($this->responseData[0]->prepaid_code);
                break;
            case 'instant':
                $metaData = $this->instantType($this->responseData[0]->face_value, $this->responseData[0]->face_value_currency);
                break;
        }

        $order->update([
            'tracking_code' => $this->responseData[0]->uid,
            'meta_data' => json_encode($metaData, true),
            'status' => 'success',
        ]);

        app('update-vendor-balance-service')->updateBalance('cysend');

        $this->sendEmailToUser($order);
        $this->sendToUserPhoneNumber($order);
    }

    /**
     * Save Prepaid Code
     *
     * @param $codeData
     * @return array
     */
    private function prepaidCodeType($codeData)
    {
        return [
            'type' => 'prepaid_code',
            'data' => $codeData
        ];
    }
    /**
     * Save Instant Data
     *
     * @param $variantValue
     * @param $currency
     * @return array
     */
    private function instantType($variantValue, $currency)
    {
        return [
            'type' => 'instant',
            'data' => [
                'variant_value' => $variantValue,
                'currency' => $currency
            ]
        ];
    }

    /**
     * Send Email To User
     *
     * @param $order
     * @return void
     */
    private function sendToUserPhoneNumber($order)
    {
        if($order->user->phone_number) {
            $productName = $order->product->display_name ?: $order->product->name;
            $orderNumber = $order->order_number ?: '';

            $messageText = "شارژیت\nاطلاعات سفارش {$orderNumber} <{$productName}>  براتون ایمیل شد\nبه شادی و لذت استفاده کنید...\n😉\nsharjit.com";

            SendMessage::dispatch($order->user->phone_number, $messageText);
        }
    }

    /**
     * Send Email To User
     *
     * @param $order
     * @return void
     */
    private function sendEmailToUser($order)
    {
        if($order->product->email_content) {
            SendOrderDetailJob::dispatch($order);
        }
    }
}
