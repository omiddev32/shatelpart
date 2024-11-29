<?php

namespace App\Category\Entities;

use App\Core\BasicEntity;
use Spatie\Translatable\HasTranslations;

class Category extends BasicEntity
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
    public $translatable = ['title', 'description'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = ['title' => 'array', 'description' => 'array'];

    /**
     * The child belongsTo a certain category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parentCategory()
    {
        return $this->belongsTo(SELF::class, 'parent', 'id');
    }

    /**
     * Get childs for this category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function childs()
    {
        return $this->hasMany(SELF::class, 'parent', 'id');
    }

    /**
     * category belongs to many products.
     *
     * @return BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(\App\Products\Entities\Products::class, 'product_categories');
    }
}
