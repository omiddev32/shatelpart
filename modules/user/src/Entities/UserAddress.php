<?php

namespace App\User\Entities;

use App\Core\BasicEntity;
use Illuminate\Database\Eloquent\Casts\Attribute;

class UserAddress extends BasicEntity
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
      'user_id', 'created_at', 'updated_at'
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['province', 'city'];

    /**
     * The address belongsTo a certain user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The address belongsTo a certain province.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function province()
    {
        return $this->belongsTo(\App\Country\Entities\Province::class);
    }

    /**
     * The address belongsTo a certain city.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function city()
    {
        return $this->belongsTo(\App\Country\Entities\City::class);
    }
}
