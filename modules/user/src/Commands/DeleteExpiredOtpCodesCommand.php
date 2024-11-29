<?php

namespace App\User\Commands;

use Illuminate\Console\Command;
use App\User\Jobs\DeleteExpiredOtpCodesJob;
use Log;

class DeleteExpiredOtpCodesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'otp:delete-expired';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete Expired Otp Codes';
    
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
        DeleteExpiredOtpCodesJob::dispatch();
        // Log::info("DeleteExpiredOrdersCommand: " . now()->format('Y-m-d H:i:s'));
    }
}