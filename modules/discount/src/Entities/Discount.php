<?php

namespace App\Discount\Entities;

use App\Core\BasicEntity;

class Discount extends BasicEntity
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
}
