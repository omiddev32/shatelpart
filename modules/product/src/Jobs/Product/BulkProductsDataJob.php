<?php

namespace App\Product\Jobs\Product;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\{SerializesModels , InteractsWithQueue};
use Illuminate\Contracts\Queue\{ShouldQueue, ShouldBeUnique};
use Illuminate\Foundation\Bus\Dispatchable;
use App\Product\Services\Product\UpsertProductApisService;

class BulkProductsDataJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Vendor service driver
     *
     * @var string
     */
    private $data;

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
        return "bulk-products-data-job-{$this->groupKey}";
    }

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $key)
    {
        $this->data = $data;
        $this->groupKey = $key;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $upsertProductService = new UpsertProductApisService();
        $upsertProductService->upsertProductsData($this->data);
    }
}
