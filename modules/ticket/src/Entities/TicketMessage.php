<?php

namespace App\Ticket\Entities;

use App\Core\BasicEntity;

class TicketMessage extends BasicEntity
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['referredTo', 'referredFrom', 'files'];

    /**
     * Get the parent imageable model (user or post).
     */
    public function modelable()
    {
        return $this->morphTo();
    }

    /**
     * The ticket belongsTo a certain user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function referredTo()
    {
        return $this->belongsTo(\App\User\Entities\Admin::class, 'referred_to_admin', 'id');
    }

    /**
     * The ticket belongsTo a certain user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function referredFrom()
    {
        return $this->belongsTo(\App\User\Entities\Admin::class, 'referred_from_admin', 'id');
    }

    /**
     * Get all of the files for the message.
     */
    public function files()
    {
        return $this->hasMany(TicketMessageFile::class);
    }
}
