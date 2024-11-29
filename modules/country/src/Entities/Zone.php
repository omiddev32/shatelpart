<?php

namespace App\Country\Entities;

use App\Core\BasicEntity;
use Spatie\Translatable\HasTranslations;

class Zone extends BasicEntity
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
    public $translatable = ['name', 'description'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = ['name' => 'array', 'description' => 'array'];

    /**
     * zone belongs to many countries.
     *
     * @return BelongsToMany
     */
    public function countries()
    {
        return $this->belongsToMany(Country::class, 'zone_countries');
    }
}
