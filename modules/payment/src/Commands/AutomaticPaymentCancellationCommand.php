<?php

namespace App\Payment\Commands;

use Illuminate\Console\Command;
use App\Payment\Jobs\AutomaticPaymentCancellationJob;
use Log;

class AutomaticPaymentCancellationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:automatic-cancellation';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatic Payment Cancellation';
    
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        AutomaticPaymentCancellationJob::dispatch();
        // Log::info("DeleteExpiredOrdersCommand: " . now()->format('Y-m-d H:i:s'));
    }
}