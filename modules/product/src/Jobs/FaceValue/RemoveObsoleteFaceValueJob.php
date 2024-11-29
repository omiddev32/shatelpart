<?php

namespace App\Product\Jobs\FaceValue;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\{SerializesModels , InteractsWithQueue};
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RemoveObsoleteFaceValueJob implements ShouldQueue
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

        if(count($faceValueIds)) {
            $this->faceValueIds = collect($faceValueIds)->map(fn($id) => "{$driver}-{$id}")->toArray();
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $faceValues = \DB::table('face_value_apis')
            ->where('vendor_id', $this->vendorId)
            ->where('product_id', "{$this->driver}-{$this->productId}")
            ->get();

        /*all faceValues*/
        $productFaceValues = [];

        foreach($faceValues as $faceValue) {
            $productFaceValues[] = $faceValue->face_value_id;
        }

        $this->deleteFaceValues(array_diff($productFaceValues, $this->faceValueIds));
    }

    /**
     * Delete Face Values
     *
     * @return void
     */
    protected function deleteFaceValues(array $ids)
    {
        \DB::table('face_value_apis')
            ->where('vendor_id', $this->vendorId)
            ->where('product_id', "{$this->driver}-{$this->productId}")
            ->whereIn('face_value_id', $ids)
            ->delete();
    }
}
