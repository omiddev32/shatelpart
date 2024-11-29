<?php

namespace App\Product\Jobs\FaceValue;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\{SerializesModels , InteractsWithQueue};
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Product\Services\Cysend\UpdateProductVariantService as UpdateCysendProductVariantService;

class UpdateProductVariantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Face values list
     *
     * @var array
     */
    private array $list;

    /**
     * Vendor service driver
     *
     * @var string
     */
    private string $driver;

    /**
     * Face value type
     *
     * @var string
     */
    private string $type;

    /**
     * Provided Products
     *
     * @var array
     */
    private array $providedProducts;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $list, string $type, string $driver, array $providedProducts = [])
    {
        $this->list = $list;
        $this->type = $type;
        $this->driver = $driver;
        $this->providedProducts = $providedProducts;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->type === 'Fixed') {
            $this->updateFixedData();
        } else {
            $this->updateRangeData();
        }
    }

    /**
     * Update fixed data
     * 
     * @return void
     */
    private function updateFixedData()
    {
        if($this->driver === 'cysend') {
            $updateProductVariantService = new UpdateCysendProductVariantService();
            $updateProductVariantService->updateFixedData($this->list, $this->providedProducts);
        } else {
            \Log::info("{$this->driver} - product variant service - NotFound");
        }
    }

    /**
     * Update range data
     * 
     * @return void
     */
    private function updateRangeData()
    {
        if($this->driver === 'cysend') {
            $updateProductVariantService = new UpdateCysendProductVariantService();
            $updateProductVariantService->updateRangeData($this->list, $this->providedProducts);

        } else {
            \Log::info("{$this->driver} - product variant service - NotFound");
        }
    }
}
