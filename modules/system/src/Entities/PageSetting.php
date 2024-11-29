<?php

namespace App\System\Entities;

use App\Core\BasicEntity;

class PageSetting extends BasicEntity
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The name of the "created at" column.
     *
     * @var string|null
     */
    const CREATED_AT = null;

    /**
     * The name of the "updated at" column.
     *
     * @var string|null
     */
    const UPDATED_AT = null;

    /**
     * page setting belongs to many products.
     *
     * @return BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(\App\Product\Entities\Product::class, 'page_setting_products');
    }
}
