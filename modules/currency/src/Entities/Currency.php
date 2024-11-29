<?php

namespace App\Currency\Entities;

use App\Core\BasicEntity;
use Spatie\Translatable\HasTranslations;
use Mavinoo\Batch\Traits\HasBatch;

class Currency extends BasicEntity
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
        'currency_name'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'currency_name' => 'array',
        'last_price_update' => 'datetime',
    ];
}