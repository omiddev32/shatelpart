<?php

namespace App\Product\Entities;

use App\Core\BasicEntity;
use Mavinoo\Batch\Traits\HasBatch;
use Lapaliv\BulkUpsert\Bulkable;

class ProductVariant extends BasicEntity
{
    use HasBatch, Bulkable;
    
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The variant belongsTo a certain vendor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vendor()
    {
        return $this->belongsTo(\App\Vendor\Entities\Vendor::class);
    }

    /**
     * The variant belongsTo a certain currency.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(\App\Currency\Entities\Currency::class, 'face_value_currency', 'iso');
    }
}
