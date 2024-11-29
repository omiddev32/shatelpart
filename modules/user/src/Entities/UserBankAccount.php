<?php

namespace App\User\Entities;

use App\Core\BasicEntity;

class UserBankAccount extends BasicEntity
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getTitleAttribute()
    {
        return "{$this->user->full_name} - {$this->bank_name} - {$this->lastFourCardNumber()}";
    }

    /**
     * Get the last four card number
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function lastFourCardNumber()
    {
        return substr($this->card_number, -4);
    }

    /**
     * The address belongsTo a certain user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
