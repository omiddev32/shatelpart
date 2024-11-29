<?php

namespace App\Ticket\Entities;

use App\Core\BasicEntity;
use Spatie\Translatable\HasTranslations;

class TicketSetting extends BasicEntity
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
    public $translatable = ['first_stage_notification_text' , 'second_stage_notification_text'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = ['first_stage_notification_text' => 'array' , 'second_stage_notification_text' => 'array'];

    /**
     * The name of the "created at" column.
     *
     * @var string|null
     */
    const CREATED_AT = null;
}
