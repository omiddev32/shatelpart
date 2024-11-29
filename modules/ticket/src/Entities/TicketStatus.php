<?php

namespace App\Ticket\Entities;

use App\Core\BasicEntity;
use Spatie\Translatable\HasTranslations;

class TicketStatus extends BasicEntity
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
    public $translatable = ['display_name', 'notification_text'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = ['display_name' => 'array', 'notification_text' => 'array'];

    /**
     * Get all of the tickets for the status.
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
