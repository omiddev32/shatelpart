<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Package Connection
    |--------------------------------------------------------------------------
    |
    | You can set a different database connection for this package. It will set
    | new connection for models Role and Permission. When this option is null,
    | it will connect to the main database, which is set up in database.php
    |
    */

    'connection'            => null,
    'rolesTable'            => 'roles',
    'roleUserTable'         => 'role_user',
    'permissionsTable'      => 'permissions',
    'permissionsRoleTable'  => 'permission_role',
    'permissionsUserTable'  => 'permission_user',

    /*
    |--------------------------------------------------------------------------
    | Slug Separator
    |--------------------------------------------------------------------------
    |
    | Here you can change the slug separator. This is very important in matter
    | of magic method __call() and also a `Slugable` trait. The default value
    | is a dot.
    |
    */

    'separator' => env('ROLES_DEFAULT_SEPARATOR', '.'),

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | If you want, you can replace default models from this package by models
    | you created. Have a look at `App\Role\Models\Role` model and
    | `App\Role\Models\Permission` model.
    |
    */

    'models' => [
        'role'          => App\User\Entities\Role::class,
        'permission'    => App\User\Entities\Permission::class,
        'defaultUser'   => config('auth.providers.admin.model'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Inheritance
    |--------------------------------------------------------------------------
    |
    | By default, the plugin is configured so that all roles inherit all
    | permissions applied to roles defined at a lower level than the role in
    | question. If this is not desired, setting the below to false will disable
    | this inheritance
    |
    */

    'inheritance' => true,

    /*
    |--------------------------------------------------------------------------
    | Roles, Permissions and Allowed "Pretend"
    |--------------------------------------------------------------------------
    |
    | You can pretend or simulate package behavior no matter what is in your
    | database. It is really useful when you are testing you application.
    | Set up what will methods hasRole(), hasPermission() and allowed() return.
    |
    */

    'pretend' => [
        'enabled' => false,
        'options' => [
            'hasRole'       => true,
            'hasPermission' => true,
            'allowed'       => true,
        ],
    ],
    /*
    |--------------------------------------------------------------------------
    | Default Migrations
    |--------------------------------------------------------------------------
    |
    | These are the default package migrations. If you publish the migrations
    | to your project, then this is not necessary and should be disabled. This
    | will enable our default migrations.
    |
    */

    'defaultMigrations' => [
        'enabled'        => true,
    ],
];
