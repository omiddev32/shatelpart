<?php

namespace App\User\Entities;

use App\User\Contracts\PermissionHasRelations as PermissionHasRelationsContract;
use App\User\Database;
use App\User\Traits\{PermissionHasRelations , Slugable};

class Permission extends Database implements PermissionHasRelationsContract
{
    use PermissionHasRelations, 
        Slugable;
    
    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
    ];

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
        'group',
        'description',
        'model',
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
        'group'         => 'string',
        'description'   => 'string',
        'model'         => 'string',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'deleted_at'    => 'datetime',
    ];
}