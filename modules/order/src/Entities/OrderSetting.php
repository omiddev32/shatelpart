<?php

namespace App\Order\Entities;

use App\Core\BasicEntity;

class OrderSetting extends BasicEntity
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The name of the "created at" column.
     *
     * @var string|null
     */
    const CREATED_AT = null;
}
