<?php

namespace App\Order\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\{SerializesModels , InteractsWithQueue};
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\User\Entities\User;
use App\Order\Entities\Order;
use App\Payment\Entities\Transaction;
use App\Message\Jobs\SendMessage;

class FailedOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Order id
     *
     * @var int
     */
    private $orderId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $order = Order::with(['product'])->find($this->orderId);
        $user = User::find($order->user_id);
        $order->update(['status', 'failed']);

        /*  */
        Transaction::where('order_id', $this->orderId)->update([
            'order_id' => null
        ]);

        $reference = rand(123456, 999999). time();

        // \Log::info($user);

        $user->purchase_amount = $user->purchase_amount - $order->price_paid;
        $user->wallet_balance = $user->wallet_balance + $order->price_paid;
        $user->withdrawable_credit = $user->withdrawable_credit + $order->price_paid;
        $user->save();

        Transaction::create([
            'user_id' => $user->id,
            'reference_number' => $reference,
            'tracking_code' => $reference,
            'amount' => $order->price_paid,
            'type' => 'BackToWallet',
            'description' => "برگشت به کیف پول",
            'reternable' => true,
            'mode' => 'Increment',
            'email'=> $user->email ?: '',
            'mobile'=> $user->phone_number ?: '',
            'status' => 'success',
            'paid_at' => now(),
        ]);

        $order->update([
            'status' => 'failed'
        ]);

        // $user->update([
        //     'purchase_amount' => $user->purchase_amount - $order->price_paid,
        //     'wallet_balance' => $user->wallet_balance + $order->price_paid,
        //     'withdrawable_credit' => $user->withdrawable_credit + $order->price_paid,
        // ]);

        /* Note: Send email to user  */

        if($user->phone_number) {
            $productName = $order->product->display_name ?: $order->product->name;
            $orderNumber = $order->order_number ?: '';

            $messageText = "شارژیت\nمتاسفانه سفارش {$orderNumber} <{$productName}>  با خطا مواجه شد\n🙈\nمبلغ پرداختی، به اعتبار شما اضافه شد\nsharjit.com";

            SendMessage::dispatch($user->phone_number, $messageText);
        }

    }
}
