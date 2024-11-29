<?php

namespace App\Country\Entities;

use App\Core\BasicEntity;
use Spatie\Translatable\HasTranslations;

class Country extends BasicEntity
{
    use HasTranslations;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

     /**
     * The attributes that translated.
     *
     * @var array
     */
    public $translatable = ['name', 'description'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = ['name' => 'array', 'description' => 'array'];

    /**
     * country belongs to many zones.
     *
     * @return BelongsToMany
     */
    public function zones()
    {
        return $this->belongsToMany(Zone::class, 'zone_countries');
    }

    /**
     * country belongs to many products.
     *
     * @return BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(\App\Product\Entities\Product::class, 'product_countries');
    }
}
