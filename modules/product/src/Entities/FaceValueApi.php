<?php

namespace App\Product\Entities;

use App\Core\BasicEntity;
use Mavinoo\Batch\Traits\HasBatch;
use Lapaliv\BulkUpsert\Bulkable;

class FaceValueApi extends BasicEntity
{
    use HasBatch, Bulkable;
    
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
}
