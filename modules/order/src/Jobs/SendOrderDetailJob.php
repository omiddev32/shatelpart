<?php

namespace App\Order\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\{SerializesModels , InteractsWithQueue};
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use App\Message\Jobs\SendMessage;
use Morilog\Jalali\Jalalian;
use Storage;

class SendOrderDetailJob implements ShouldQueue
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
        $order = $this->order;
        $orderData = [];
        $meta = json_decode($order->meta_data, true);

        if($meta['type'] == 'prepaid_code') {
            $orderData = [
                'code' => $meta['data']['code'],
                'serial' => $meta['data']['serial'],
                'expiration' => $meta['data']['expiration'],
            ];
        }

        $emailTemplate = str_replace([
            '{{userFirstName}}',
            '{{userLastName}}',
            '{{userNationalCode}}',
            '{{userEmail}}',
            '{{userPhoneNumber}}',
            '{{productName}}',
            '{{imageLink}}',
            '{{orderNumber}}',
            '{{variantValue}}',
            '{{variantCurrency}}',
            '{{orderStatus}}',
            '{{createdAt}}',
            '{{pricePaid}}',
            '{{referenceNumber}}',
            '{{trackingCode}}',
            '{{prepaidCode}}',
            '{{prepaidSerial}}',
            '{{prepaidExpiration}}',
        ],[
            $order->user->first_name,
            $order->user->last_name,
            $order->user->code,
            $order->user->email,
            $order->user->phone_number,
            $order->product->display_name ?: $order->product->name,
            $order->product->image ? Storage::disk('products')->url($order->product->image) : '',
            $order->order_number ?: '',
            $order->variant_value,
            $order->variant->currency->currency_name,
            \App\Order\Enums\OrderStatusEnum::instanceFromKey($order->status)->value(),
            Jalalian::forge($order->created_at)->format("Y-m-d H:i:s"),
            number_format(($order->product_price + $order->tax_price) / 10) . 'تومان',
            $order->reference_number,
            $order->tracking_code,
            isset($orderData['code']) ? $orderData['code'] : '',
            isset($orderData['serial']) ? $orderData['serial'] : '',
            isset($orderData['expiration']) ? $orderData['expiration'] : '',
        ], $order->product->email_content);

        Mail::to($order->user->email)
          ->send(new \App\System\Mails\SendMail('خرید ' . $order->product->display_name ?: $order->product->name, 'order::mails.orderDetail', ['body' => $emailTemplate]));

        \DB::table('orders')->where('id', $order->id)->update([
            'send_to_user' => true
        ]);
    }
}
