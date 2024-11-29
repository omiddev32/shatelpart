<?php

namespace App\Vendor\Entities;

use App\Core\BasicEntity;

class Vendor extends BasicEntity
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['token', 'extra_data'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'extra_data'             => 'array',
        'latest_product_updates' => 'datetime',
        'created_at'             => 'datetime',
        'updated_at'             => 'datetime',
    ];

    /**
     * vendor belongs to many products.
     *
     * @return BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(\App\Product\Entities\Product::class, 'product_vendors');
    }

    /**
     * Get prducts repository for this vendor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productApis()
    {
        return $this->hasMany(\App\Product\Entities\ProductApi::class);
    }
}
