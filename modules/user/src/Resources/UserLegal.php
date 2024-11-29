<?php

namespace App\User\Resources;

use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\{ID, BelongsTo, Text};
use App\System\NovaResource as Resource;

class UserLegal extends Resource
{
    /**
     * Get the logical group associated with the resource.
     *
     * @return string
     */
    public static function group()
    {
        return __("Users");
    }

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __("Legal Information");
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __("Legal Information");
    }

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\User\Entities\UserLegal';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'company_name';

    /**
     * Determine permission access this resource
     *
     * @var string
     */
    public static $permission = 'users';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = ['company_name'];

    /**
     * The relationships that should be eager loaded when performing an index query.
     *
     * @var array
     */
    public static $with = ['user', 'province', 'city'];

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

            BelongsTo::make(__("User"), 'user', User::class)
                ->searchable(),

            Text::make(__("Name"), 'company_name')
                ->help(__("Name of organization, company, institution"))
                ->rules('required'),

            Text::make(__("Economic Code"), 'economic_code')
                ->rules('required')
                ->creationRules('unique:user_legals,economic_code')
                ->updateRules('unique:user_legals,economic_code,{{resourceId}}'),

            Text::make(__("Registration Number"), 'registration_number')
                ->rules('required')
                ->creationRules('unique:user_legals,registration_number')
                ->updateRules('unique:user_legals,registration_number,{{resourceId}}'),
                
            Text::make(__("Phone Number"), 'phone')
                ->rules('required')
                ->creationRules('unique:user_legals,phone')
                ->updateRules('unique:user_legals,phone,{{resourceId}}'),

            BelongsTo::make(__("Province"), 'province', \App\Country\Resources\Province::class)
                ->searchable(),

            BelongsTo::make(__("City"), 'city', \App\Country\Resources\City::class)
                ->searchable(),
                         
            Text::make(__("Postal Code"), 'postal_code')
                ->rules('required')
                ->creationRules('unique:user_legals,postal_code')
                ->updateRules('unique:user_legals,postal_code,{{resourceId}}'),
                
        ];
    }


    /**
     * Determine if this resource is available for navigation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public static function availableForNavigation(Request $request)
    {
        return false;
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