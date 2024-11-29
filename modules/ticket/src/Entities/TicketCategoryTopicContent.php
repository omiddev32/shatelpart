<?php

namespace App\Ticket\Entities;

use App\Core\BasicEntity;
use Illuminate\Database\Eloquent\Casts\Attribute;

class TicketCategoryTopicContent extends BasicEntity
{

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = ['keywords' => 'array'];

    // /**
    //  * Get the title.
    //  *
    //  * @return \Illuminate\Database\Eloquent\Casts\Attribute
    //  */
    // protected function title(): Attribute
    // {
    //     return new Attribute(get: fn ($value) => $value);
    // }

    /**
     * The content belongsTo a certain ticket category topic.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ticketCategoryTopic()
    {
        return $this->belongsTo(\App\Ticket\Entities\TicketCategoryTopic::class);
    }
}
