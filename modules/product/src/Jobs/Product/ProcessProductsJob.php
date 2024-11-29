<?php

namespace App\Product\Jobs\Product;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\{SerializesModels , InteractsWithQueue};
use Illuminate\Contracts\Queue\{ShouldQueue, ShouldBeUnique};
use Illuminate\Foundation\Bus\Dispatchable;
use App\Product\Entities\ProductApi;
use App\Product\Jobs\Product\UpsertProductApisJob;
use App\Product\Jobs\Variant\RemoveObsoleteVariantsJob;
use App\Product\Jobs\FaceValue\RemoveObsoleteFaceValuesJob;

class ProcessProductsJob implements ShouldQueue, ShouldBeUnique
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
     * Values
     *
     * @var array
     */
    private $values;

    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public $uniqueFor = 3600;
 
    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return "process-products-job-{$this->driver}";
    }

    /**
     * Create a new job instance.
     *
     * @param $vendorId
     * @param string $driver = 'cysend'
     * @param $values
     * @return void
     */
    public function __construct($vendorId, string $driver, $values)
    {
        $this->vendorId = $vendorId;
        $this->driver = $driver;
        $this->values = $values;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if(is_array($this->values)) {
            foreach(collect($this->values)->chunk(200)->toArray() as $key => $group) {
                $delayNumber = ($key + 1) * 15;
                UpsertProductApisJob::dispatch($this->driver, $group, $key)
                    ->delay(now()->addSeconds($delayNumber));
                RemoveObsoleteVariantsJob::dispatch($this->vendorId, $this->driver, $group, $this->getProvidedProducts())
                    ->delay(now()->addSeconds($delayNumber + 10));
                RemoveObsoleteFaceValuesJob::dispatch($this->vendorId, $this->driver, $group)
                    ->delay(now()->addSeconds($delayNumber + 30));
            }

            $this->updateVendor();
        }
    }

    /**
     * Get provided products
     *
     * @return void
     */
    protected function updateVendor()
    {
        $count = count($this->values);
        if($count > 0) {
            \DB::table('vendors')->where('id', $this->vendorId)->update([
                'number_of_products_is_not_provided' => $count,
                'latest_product_updates' => now(),
            ]);
        }
    }

    /**
     * Get provided products
     *
     * @return array
     */
    protected function getProvidedProducts()
    {
        $providedProducts = [];

        foreach(
            \DB::table('product_apis')
                ->select(['id', 'vendor_id', 'connected_to', 'product_id'])                
                ->where('vendor_id', $this->vendorId)
                ->where('connected_to', '!=', null)
                ->get() as $product
            ) {
            $providedProducts[$product->product_id] = $product->connected_to;
        }

        return $providedProducts;
    }
}
