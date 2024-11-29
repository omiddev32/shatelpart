<?php

namespace App\Product\Commands;

use Illuminate\Console\Command;
use App\Product\Jobs\FaceValue\GetFaceValuesJob;
use Log;

class CysendFaceValueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'faceValue:cysend';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update cysend face values';
    
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
        GetFaceValuesJob::dispatch('cysend');
        // Log::info("Get Cysend Face Value Api " . now()->format('Y-m-d H:i:s'));
    }
}