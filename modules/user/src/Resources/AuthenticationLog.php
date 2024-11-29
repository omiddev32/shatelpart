<?php

namespace App\User\Resources;

use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\{ID, MorphTo, Text, Boolean, DateTime, KeyValue};
use App\System\NovaResource as Resource;

class AuthenticationLog extends Resource
{
    /**
     * Get the logical group associated with the resource.
     *
     * @return string
     */
    public static function group()
    {
        return __("Logs");
    }

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __("Authentication Logs");
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __("Authentication Log");
    }

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \Rappasoft\LaravelAuthenticationLog\Models\AuthenticationLog::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * Determine permission access this resource
     *
     * @var string
     */
    public static $permission = 'logs';

    /**
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'authenticatable.first_name',
        'ip_address',
        'user_agent',
        'login_at',
        'login_successful',
        'logout_at',
        'cleared_by_user',
        'location'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make(__('ID'),'id')
                ->sortable()
                ->onlyOnIndex(),

            MorphTo::make(__('User'), 'authenticatable')
                ->types([
                    Admin::class
                ]),

            Text::make(__('Ip Address'), 'ip_address')
                ->sortable(),

            Text::make(__('User Agent'), 'user_agent')
                ->hideFromIndex()
                ->sortable(),

            DateTime::make(__('Login At'), 'login_at')
                ->sortable(),

            Boolean::make(__('Login Successful'), 'login_successful')
                ->sortable(),

            DateTime::make(__('Logout At'), 'logout_at')
                ->sortable(),

            // Boolean::make(__('Cleared By User'), 'cleared_by_user')
            //     ->sortable(),

            // KeyValue::make(__('Location'), 'location')
            //     ->sortable(),

        ];
    }

    /**
     * Determine if the current user can create new resources.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    /**
     * Determine if the current user can edit resources.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function authorizedToUpdate(Request $request)
    {
        return false;
    }

    /**
     * Determine if the current user can replicate the given resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function authorizedToReplicate(Request $request)
    {
        return false;
    }

    /**
     * Determine if the current user can delete resources.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function authorizedToDelete(Request $request)
    {
        return false;
    }
}