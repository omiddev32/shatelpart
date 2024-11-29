<?php

namespace App\Order\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\{SerializesModels , InteractsWithQueue};
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class DeleteExpiredOrdersJob implements ShouldQueue
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
        $dateTime = now()->format('Y-m-d H:i:s');

        DB::table('orders')
            ->where('status', 'initial')
            ->where('expires_at', '<=', $dateTime)
            ->delete();
    }
}
