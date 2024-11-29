<?php

namespace App\Product\Entities;

use App\Core\BasicEntity;
use Spatie\Translatable\HasTranslations;
use Mavinoo\Batch\Traits\HasBatch;

class Product extends BasicEntity
{
    use HasTranslations, HasBatch;

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
    public $translatable = [
        'display_name', 'introduction', 'application', 'usage_method', 'email_content'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
      'created_at', 'updated_at', 'disabled_until', 'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'display_name' => 'array', 
        'meta_data' => 'json', 
        'settings_data' => 'json', 
        'finance_data' => 'json', 
        'beneficiary_information' => 'array', 
        'images_data' => 'array', 
        'hashtags' => 'array', 
        'disabled_until' => 'datetime', 
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The product belongsTo a certain brand.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function brand()
    {
        return $this->belongsTo(\App\Brand\Entities\Brand::class);
    }

    /**
     * The product belongsTo a certain sub brand.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subBrand()
    {
        return $this->belongsTo(\App\Brand\Entities\Brand::class);
    }

    /**
     * The product belongsTo a certain type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function productType()
    {
        return $this->belongsTo(ProductType::class);
    }

    /**
     * The product belongsTo a certain zone.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function zone()
    {
        return $this->belongsTo(\App\Country\Entities\Zone::class);
    }

    /**
     * product belongs to many usings.
     *
     * @return BelongsToMany
     */
    public function productUsings()
    {
        return $this->belongsToMany(Using::class, 'product_usings');
    }

    /**
     * product belongs to many delivery types.
     *
     * @return BelongsToMany
     */
    public function deliveryTypes()
    {
        return $this->belongsToMany(DeliveryType::class, 'product_delivery_types');
    }

    /**
     * product belongs to many questions.
     *
     * @return BelongsToMany
     */
    public function questions()
    {
        return $this->belongsToMany(\App\Question\Entities\Question::class, 'product_questions');
    }

    /**
     * product belongs to many vendors.
     *
     * @return BelongsToMany
     */
    public function vendors()
    {
        return $this->belongsToMany(\App\Vendor\Entities\Vendor::class, 'product_vendors');
    }

    /**
     * product belongs to many countries.
     *
     * @return BelongsToMany
     */
    public function countries()
    {
        return $this->belongsToMany(\App\Country\Entities\Country::class, 'product_countries');
    }

    /**
     * product belongs to many categories.
     *
     * @return BelongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany(\App\Category\Entities\Category::class, 'product_categories');
    }

    /**
     * product belongs to many tags.
     *
     * @return BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany(\App\Tag\Entities\Tag::class, 'product_tags');
    }

    /**
     * Get variants for this prduct.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}
