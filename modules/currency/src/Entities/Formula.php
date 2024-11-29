<?php

namespace App\Currency\Entities;

use App\Core\BasicEntity;

class Formula extends BasicEntity
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The formula belongsTo a certain formula group.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function formulaGroup()
    {
        return $this->belongsTo(FormulaGroup::class);
    }
}
