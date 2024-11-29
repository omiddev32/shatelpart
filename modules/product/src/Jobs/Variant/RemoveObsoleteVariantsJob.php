<?php

namespace App\Product\Jobs\Variant;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\{SerializesModels , InteractsWithQueue};
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Product\Jobs\Variant\RemoveObsoleteVariantJob;

class RemoveObsoleteVariantsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Vendor id
     *
     * @var integer
     */
    private $vendorId;

    /**
     * Vendor service driver
     *
     * @var string
     */
    private string $driver;

    /**
     * List
     *
     * @var array
     */
    private $list;
    
    /**
     * Provided products
     *
     * @var array
     */
    private $providedProducts = [];
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($vendorId, string $driver = 'cysend', $list, array $providedProducts = [])
    {
        $this->vendorId = $vendorId;
        $this->driver = $driver;
        $this->list = $list;
        $this->providedProducts = $providedProducts;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        collect($this->list)->whereIn('product_id', array_keys($this->providedProducts))->each(function($product) {
            RemoveObsoleteVariantJob::dispatch($this->vendorId, $this->driver, $this->providedProducts[$product->product_id], $product->face_value_ids);
        });
    }
}
