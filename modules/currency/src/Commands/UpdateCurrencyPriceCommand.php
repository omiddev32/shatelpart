<?php

namespace App\Currency\Commands;

use Illuminate\Console\Command;
use App\Currency\Jobs\UpdateCurrencyPriceJob;
use Log;

class UpdateCurrencyPriceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currencies:update';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update currencies price';
    
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
        UpdateCurrencyPriceJob::dispatch();
        // Log::info("Update currencies price Api " . now()->format('Y-m-d H:i:s'));
    }
}