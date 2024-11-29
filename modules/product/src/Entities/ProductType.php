<?php

namespace App\Product\Entities;

use App\Core\BasicEntity;
use Spatie\Translatable\HasTranslations;

class ProductType extends BasicEntity
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
}
