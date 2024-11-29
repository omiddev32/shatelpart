<?php

namespace App\Ticket\Entities;

use App\Core\BasicEntity;

class Ticket extends BasicEntity
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
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_referred_at' => 'datetime',
    ];
    
    /**
     * The ticket belongsTo a certain user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(\App\User\Entities\User::class);
    }

    /**
     * The ticket belongsTo a certain order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(\App\Order\Entities\Order::class);
    }

    /**
     * The ticket belongsTo a certain user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function firstReferredTo()
    {
        return $this->belongsTo(\App\User\Entities\Admin::class, 'first_referred_to_admin', 'id');
    }
    
    /**
     * The ticket belongsTo a certain user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function referredTo()
    {
        return $this->belongsTo(\App\User\Entities\Admin::class, 'last_referred_to_admin', 'id');
    }

    /**
     * The ticket referred To Organization.
     * 
     * @return BelongsToMany
     */
    // public function firstReferredToOrganization()
    // {
    //     return $this->belongsTo(\App\User\Entities\Organization::class, 'first_referred_to_organization', 'id');
    // }

    /**
     * The ticket referred To Organization.
     * 
     * @return BelongsToMany
     */
    // public function referredToOrganization()
    // {
    //     return $this->belongsTo(\App\User\Entities\Organization::class, 'last_referred_to_organization', 'id');
    // }
 
    /**
     * The ticket belongsTo a certain ticket category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ticketCategory()
    {
        return $this->belongsTo(\App\Ticket\Entities\TicketCategory::class);
    }

    /**
     * The ticket belongsTo a certain ticket category topic.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ticketCategoryTopic()
    {
        return $this->belongsTo(\App\Ticket\Entities\TicketCategoryTopic::class);
    }

    /**
     * The ticket belongsTo a certain ticket category status.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ticketStatus()
    {
        return $this->belongsTo(\App\Ticket\Entities\TicketStatus::class);
    }

    /**
     * Get all of the messages for the ticket.
     */
    public function messages()
    {
        return $this->hasMany(TicketMessage::class, 'ticket_id', 'id');
    }
}
