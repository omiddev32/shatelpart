<?php

namespace App\Product\Jobs\Product;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\{SerializesModels , InteractsWithQueue};
use Illuminate\Contracts\Queue\{ShouldQueue, ShouldBeUnique};
use Illuminate\Foundation\Bus\Dispatchable;
use App\Product\Jobs\Product\BulkProductsDataJob;
use Storage;

class UpsertProductApisJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
     * Group Key
     *
     * @var string
     */
    private string $groupKey;

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
        return "upsert-product-apis-job-{$this->groupKey}";
    }

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $driver, $list, $key)
    {
        $this->driver = $driver;
        $this->list = $list;
        $this->groupKey = $key;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $list = [];
        if($this->driver === 'cysend') {
            $list = $this->makeCysendList();
        } else if($this->driver === 'gifthub') {
            \Log::info("{$this->driver} - product apis service - NotFound");
        } else {
            \Log::info("{$this->driver} - product apis service - NotFound");
        }

        BulkProductsDataJob::dispatch($list, $this->groupKey);
    }

    // protected function makeGiftHubList()
    // {
    //     $list = [];
    //     foreach($this->list as $product) {
    //         if($product->name) {
    //             $list[] = [
    //                 'vendor_id' => 4,
    //                 // 'zone' => $product->country_zone,
    //                 'product_id' => "gifthub-{$product->productId}",
    //                 'name' => $product->name,
    //                 'description' => json_encode($product->product_description, true),
    //                 'logo_url' => Storage::disk('products')->url('Sharjit-Gift-Card-Template.png'),
    //                 'type' => $product->productType,
    //                 'promotion' => false,
    //                 'maintenance' => $product->state !== 'ACTIVE',
    //                 'beneficiary_information' => json_encode([], true),
    //                 'usage_instructions' => json_encode(isset([], true),
    //                 'face_values' => json_encode($product->face_value_ids, true),
    //                 'countries' => json_encode($product->countries, true),
    //                 'created_at' => now(),
    //                 'updated_at' => now(),
    //             ];
    //         }
    //     }
    //     return $list;
    // }

    // protected function getGiftHubFaceValues()
    // {
    //     collect()
    // }

    protected function makeCysendList()
    {
        $list = [];
        foreach($this->list as $product) {
            if($product->product_name) {
                $list[] = [
                    'vendor_id' => 2,
                    'zone' => $product->country_zone,
                    'product_id' => "cysend-{$product->product_id}",
                    'name' => $product->product_name,
                    'description' => json_encode($product->product_description, true),
                    'logo_url' => "https://www.cysend.com" . $product->logo_url,
                    'type' => $product->type,
                    'promotion' => $product->promotion,
                    'maintenance' => $product->maintenance,
                    'beneficiary_information' => json_encode($product->beneficiary_information, true),
                    'usage_instructions' => json_encode(isset($product->usage_instructions) ? $product->usage_instructions : [], true),
                    'face_values' => json_encode($product->face_value_ids, true),
                    'countries' => json_encode($product->countries, true),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        return $list;
    }
}
