<?php

namespace App\Brand\Entities;

use App\Core\BasicEntity;
use Spatie\Translatable\HasTranslations;

class Brand extends BasicEntity
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
     * The child belongsTo a certain brand.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(SELF::class, 'brand_id', 'id');
    }

    /**
     * Get childs for this brands.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subBrands()
    {
        return $this->hasMany(SELF::class, 'brand_id', 'id');
    }

    /**
     * Get products for this brand.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany(\App\Product\Entities\Product::class);
    }
}
