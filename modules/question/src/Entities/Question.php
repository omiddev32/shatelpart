<?php

namespace App\Question\Entities;

use App\Core\BasicEntity;
use Spatie\Translatable\HasTranslations;

class Question extends BasicEntity
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
    public $translatable = ['question' , 'answer'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = ['question' => 'array' , 'answer' => 'array'];

    /**
     * question belongs to many products.
     *
     * @return BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(\App\Product\Entities\Product::class, 'product_questions');
    }
}
