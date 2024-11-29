<?php

namespace App\Tag\Entities;

use App\Core\BasicEntity;

class Tag extends BasicEntity
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
}
