<?php

namespace App\Ticket\Entities;

use App\Core\BasicEntity;
use Spatie\Translatable\HasTranslations;

class TicketCategoryTopic extends BasicEntity
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
    public $translatable = ['title'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = ['title' => 'array'];

    /**
     * The topic belongsTo a certain ticket category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ticketCategory()
    {
        return $this->belongsTo(\App\Ticket\Entities\TicketCategory::class);
    }

    /**
     * Referral default admin user
     *
     * @return BelongsToMany
     */
    public function admin()
    {
        return $this->belongsTo(\App\User\Entities\Admin::class);
    }

    /**
     * Get all of the conents for the topic.
     */
    public function ticketCategoryTopicContents()
    {
        return $this->hasMany(TicketCategoryTopicContent::class);
    }


    /**
     * Get all of the tickets for the topic.
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
