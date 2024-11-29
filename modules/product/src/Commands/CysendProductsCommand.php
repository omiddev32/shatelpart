<?php

namespace App\Product\Commands;

use Illuminate\Console\Command;
use App\Product\Jobs\Product\GetProductsJob;
use Log;

class CysendProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:cysend';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update cysend products';
    
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
        GetProductsJob::dispatch('cysend');
        // Log::info("Get Cysend Product Api " . now()->format('Y-m-d H:i:s'));
    }
}