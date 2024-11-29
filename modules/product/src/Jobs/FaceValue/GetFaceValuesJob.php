<?php

namespace App\Product\Jobs\FaceValue;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\{SerializesModels , InteractsWithQueue};
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Product\Jobs\FaceValue\{UpdateFaceValuesJob, UpdateProductVariantJob};
use App\Vendor\Services\VendorService;
use App\Product\Entities\ProductApi;
use App\Product\Jobs\Variant\UpdateProductPriceJob;

class GetFaceValuesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Vendor service driver
     *
     * @var string
     */
    private string $driver;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $driver = 'cysend')
    {
        $this->driver = $driver;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Log::info('Get Face Values');

        $service = new VendorService($this->driver);
        $values = $service->getFaceValues();
        $providedProducts = $this->getProvidedProducts($this->getVendorId());

        if (property_exists($values, 'fixed')) {
            foreach(collect($values->fixed)->chunk(200)->toArray() as $key => $group) {
                $delayNumber = ($key + 1) * 7;
                UpdateFaceValuesJob::dispatch($group, 'Fixed', $this->driver)
                    ->delay(now()->addSeconds($delayNumber));
                UpdateProductVariantJob::dispatch($group, 'Fixed', $this->driver, $providedProducts)
                    ->delay(now()->addSeconds($delayNumber));
            }
        }
        if (property_exists($values, 'range')) {
            foreach(collect($values->range)->chunk(200)->toArray() as $key => $group) {
                $delayNumber = ($key + 1) * 8;
                UpdateFaceValuesJob::dispatch($group, 'Range', $this->driver)
                    ->delay(now()->addSeconds($delayNumber));
                UpdateProductVariantJob::dispatch($group, 'Range', $this->driver, $providedProducts)
                    ->delay(now()->addSeconds($delayNumber));
            }
        }

    }

    /**
     * Get provided products
     *
     * @return array
     */
    protected function getProvidedProducts($vendorId)
    {
        $providedProducts = [];

        foreach(
            \DB::table('product_apis')
                ->select(['id', 'vendor_id', 'connected_to', 'product_id'])                
                ->where('vendor_id', $vendorId)
                ->where('connected_to', '!=', null)
                ->get() as $product
            ) {

            $providedProducts[str_replace("{$this->driver}-", '', $product->product_id)] = $product->connected_to;

            if($this->driver == 'cysend') {
                UpdateProductPriceJob::dispatch($product->connected_to)
                    ->delay(now()->addminutes(5));
            }

        }

        return $providedProducts;
    }

    /**
     * Get vendor id.
     *
     * @return integer
     */
    protected function getVendorId()
    {
        $vendor = \DB::table('vendors')->select(['id', 'service_name'])->where('service_name', $this->driver)->first();
        return $vendor ? $vendor->id : null;
    }
}
