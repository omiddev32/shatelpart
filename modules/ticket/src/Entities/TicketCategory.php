<?php

namespace App\Ticket\Entities;

use App\Core\BasicEntity;
use Spatie\Translatable\HasTranslations;

class TicketCategory extends BasicEntity
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
    public $translatable = ['title' , 'description'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = ['title' => 'array' , 'description' => 'array'];

    /**
     * category belongs to many organization.
     *
     * @return BelongsToMany
     */
    public function organizations()
    {
        return $this->belongsToMany(\App\User\Entities\Organization::class, 'ticket_category_organizations');
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
     * Get all of the topics for the category.
     */
    public function ticketCategoryTopics()
    {
        return $this->hasMany(TicketCategoryTopic::class);
    }

    /**
     * Get all of the tickets for the category.
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
