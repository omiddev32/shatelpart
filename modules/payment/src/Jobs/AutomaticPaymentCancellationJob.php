<?php

namespace App\Payment\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\{SerializesModels , InteractsWithQueue};
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Payment\Enums\TransactionStatusEnum;
use App\Payment\Entities\Transaction;

class AutomaticPaymentCancellationJob implements ShouldQueue
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
        $transactionsId = [];
        Transaction::select('id', 'status', 'created_at')->where('status', TransactionStatusEnum::INIT)->get()->each(function($transaction) use(& $transactionsId){
            if(now()->format('Y-m-d H:i:s') >= $transaction->created_at->addMinutes(20)->format('Y-m-d H:i:s')) {
                $transactionsId[] = $transaction->id;
            }
        });

        Transaction::select('id', 'status')->whereIn('id', $transactionsId)->update(['status' => TransactionStatusEnum::FAILED]);
    }
}
