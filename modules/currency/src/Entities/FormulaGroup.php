<?php

namespace App\Currency\Entities;

use App\Core\BasicEntity;

class FormulaGroup extends BasicEntity
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * formula group belongs to many currencies.
     *
     * @return BelongsToMany
     */
    public function includeCurrencies()
    {
        return $this->belongsToMany(Currency::class, 'formula_group_currencies');
    }

    /**
     * formula group belongs to many currencies.
     *
     * @return BelongsToMany
     */
    public function exceptCurrencies()
    {
        return $this->belongsToMany(Currency::class, 'formula_group_except_currencies');
    }

    /**
     * Get formulas for this formula group.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function formulas()
    {
        return $this->hasMany(Formula::class);
    }
}
