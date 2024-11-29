<?php

namespace App\Order\Commands;

use Illuminate\Console\Command;
use App\Order\Jobs\DeleteExpiredOrdersJob;
use Log;

class DeleteExpiredOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:delete-expired';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete Expired Orders';
    
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
        DeleteExpiredOrdersJob::dispatch();
        // Log::info("DeleteExpiredOrdersCommand: " . now()->format('Y-m-d H:i:s'));
    }
}