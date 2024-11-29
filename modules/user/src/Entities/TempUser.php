<?php

namespace App\User\Entities;

use App\Core\BasicEntity;

class TempUser extends BasicEntity
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
}
