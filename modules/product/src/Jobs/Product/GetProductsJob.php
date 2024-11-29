<?php

namespace App\Product\Jobs\Product;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\{SerializesModels , InteractsWithQueue};
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Product\Jobs\Product\ProcessProductsJob;
use App\Vendor\Services\VendorService;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class GetProductsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Vendor service driver
     *
     * @var string
     */
    private string $driver;

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
        return "get-products-job-{$this->driver}";
    }

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
        if($vendorId = $this->getVendorId()) {
            \Log::info('Call Products Api');
            try {
                $service = new VendorService($this->driver);
                $values = $service->getProducts();

                if(is_array($values)) {
                    ProcessProductsJob::dispatch($vendorId, $this->driver, $values);
                } else {
                    throw new \Exception("Get Product Api Error");
                }
                
            } catch (\Exception $e) {
                \Log::info($e->getMessage());
            }

        } else {
            \Log::info('Vendor not exists');
        }
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
