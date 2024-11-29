<?php

namespace App\Product\Jobs\FaceValue;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\{SerializesModels , InteractsWithQueue};
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Product\Services\Cysend\UpdateFaceValueService as UpdateCysendFaceValueService;

class UpdateFaceValuesJob implements ShouldQueue
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
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $list, string $type, string $driver)
    {
        $this->list = $list;
        $this->type = $type;
        $this->driver = $driver;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->type === 'Fixed') {
            $this->upsertFixedData();
        } else {
            $this->upsertRangeData();
        }
    }

    /**
     * Update fixed data
     * 
     * @return void
     */
    private function upsertFixedData()
    {
        if($this->driver === 'cysend') {
            $updateFaceValueService = new UpdateCysendFaceValueService();
            $updateFaceValueService->upsertFixedData($this->list);
        } else {
            \Log::info("{$this->driver} - face value service - NotFound");
        }
    }

    /**
     * Update range data
     * 
     * @return void
     */
    private function upsertRangeData()
    {
        if($this->driver === 'cysend') {
            $updateFaceValueService = new UpdateCysendFaceValueService();
            $updateFaceValueService->upsertRangeData($this->list);
        } else {
            \Log::info("{$this->driver} - face value service - NotFound");
        }
    }
}
