<?php

namespace App\Ticket\Entities;

use App\Core\BasicEntity;

class TicketMessageFile extends BasicEntity
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
}
