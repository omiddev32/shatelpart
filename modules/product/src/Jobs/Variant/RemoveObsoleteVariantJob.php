<?php

namespace App\Product\Jobs\Variant;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\{SerializesModels , InteractsWithQueue};
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Product\Jobs\Variant\UpdateProductPriceJob;

class RemoveObsoleteVariantJob implements ShouldQueue
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
     * Product id
     *
     * @var integer
     */
    private $productId;
    
    /**
     * Face value ids
     *
     * @var array
     */
    private $faceValueIds = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($vendorId, string $driver, $productId, array $faceValueIds = [])
    {
        $this->vendorId = $vendorId;
        $this->driver = $driver;
        $this->productId = $productId;
        $this->faceValueIds = $faceValueIds;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $variants = \DB::table('product_variants')
            ->where('vendor_id', $this->vendorId)
            ->where('product_id', $this->productId)
            ->get();

        /*all variants*/
        $productVariants = [];

        foreach($variants as $variant) {
            $productVariants[] = $variant->face_value_id;
        }

        $this->deleteVariants(array_diff($productVariants, $this->faceValueIds));

        if($this->driver == 'cysend') {
            UpdateProductPriceJob::dispatch($this->productId)->delay(now()->addSeconds(10));
        }
    }

    /**
     * Delete Variants
     *
     * @return void
     */
    protected function deleteVariants(array $ids)
    {
        \DB::table('product_variants')
            ->where('vendor_id', $this->vendorId)
            ->where('product_id', $this->productId)
            ->whereIn('face_value_id', $ids)
            ->delete();
    }
}
