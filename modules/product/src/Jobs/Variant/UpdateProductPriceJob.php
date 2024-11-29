<?php

namespace App\Product\Jobs\Variant;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\{SerializesModels , InteractsWithQueue};
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Product\Services\Variant\UpdateProductPriceService;

class UpdateProductPriceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Product id
     *
     * @var integer
     */
    private $productId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($productId)
    {
        $this->productId = $productId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        UpdateProductPriceService::updatePrice($this->productId);
    }
}
