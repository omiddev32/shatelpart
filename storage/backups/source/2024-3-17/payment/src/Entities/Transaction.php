<?php

namespace App\Payment\Entities;

use App\Core\BasicEntity;

class Transaction extends BasicEntity
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
        'deleted_at' => 'datetime',
        'paid_at'    => 'datetime',
    ];

    /**
     * The user belongsTo a certain transaction.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(\App\User\Entities\User::class);
    }

    /**
     * The admin belongsTo a certain transaction.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function admin()
    {
        return $this->belongsTo(\App\User\Entities\Admin::class);
    }

    /**
     * The user bank account belongsTo a certain transaction.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userBankAccount()
    {
        return $this->belongsTo(\App\User\Entities\UserBankAccount::class);
    }
}
