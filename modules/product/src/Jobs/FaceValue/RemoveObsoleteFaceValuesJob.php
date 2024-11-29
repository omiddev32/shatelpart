<?php

namespace App\Product\Jobs\FaceValue;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\{SerializesModels , InteractsWithQueue};
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Product\Jobs\FaceValue\RemoveObsoleteFaceValueJob;

class RemoveObsoleteFaceValuesJob implements ShouldQueue
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
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($vendorId, string $driver, $list)
    {
        $this->vendorId = $vendorId;
        $this->driver = $driver;
        $this->list = $list;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        collect($this->list)->each(function($product) {
            RemoveObsoleteFaceValueJob::dispatch($this->vendorId, $this->driver, "{$this->driver}-{$product->product_id}", $product->face_value_ids);
        });
    }
}
