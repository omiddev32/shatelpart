<?php

namespace App\User\Entities;

use App\Core\BasicEntity;
use Spatie\Translatable\HasTranslations;

class Organization extends BasicEntity
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
    public $translatable = ['name' , 'description'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = ['name' => 'array' , 'description' => 'array'];

    /**
     * Get all of the admins for the organization.
     */
    public function admins()
    {
        return $this->hasMany(Admin::class);
    }

    /**
     * Organization manager user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
