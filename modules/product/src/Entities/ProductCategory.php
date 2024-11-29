<?php

namespace App\Product\Entities;

use App\Core\BasicEntity;

class ProductCategory extends BasicEntity
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
}
