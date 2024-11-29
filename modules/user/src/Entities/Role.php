<?php

namespace App\User\Entities;

// use Illuminate\Database\Eloquent\SoftDeletes;
use App\User\Contracts\RoleHasRelations as RoleHasRelationsContract;
use App\User\Database;
use App\User\Traits\{RoleHasRelations , Slugable};

class Role extends Database implements RoleHasRelationsContract
{
    use RoleHasRelations , 
    // SoftDeletes , 
    Slugable;
    
    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'level',
        'status',
        'organization_id',
    ];

    /**
     * Typecast for protection.
     *
     * @var array
     */
    protected $casts = [
        'id'            => 'integer',
        'name'          => 'string',
        'slug'          => 'string',
        'description'   => 'string',
        'level'         => 'integer',
        'status'        => 'boolean',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'deleted_at'    => 'datetime',
    ];
}