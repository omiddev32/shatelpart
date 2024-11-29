<?php

namespace App\Country\Entities;

use App\Core\BasicEntity;

class Province extends BasicEntity
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The province connected to certain country.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get all of the cities for the product.
     */
    public function cities()
    {
        return $this->hasMany(City::class);
    }
}
