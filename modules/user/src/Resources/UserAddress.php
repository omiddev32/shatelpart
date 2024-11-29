<?php

namespace App\User\Resources;

use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\{ID, Text, BelongsTo, Boolean};
use App\System\NovaResource as Resource;

class UserAddress extends Resource
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
        return __("User Addresses");
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __("User Address");
    }

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\User\Entities\UserAddress';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'address';

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
    public static $search = ['address', 'postal_code'];

    /**
     * The relationships that should be eager loaded when performing an index query.
     *
     * @var array
     */
    public static $with = ['province', 'city'];

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

            BelongsTo::make(__("Province"), 'province', \App\Country\Resources\Province::class)
                ->showOnPreview(),

            BelongsTo::make(__("City"), 'city', \App\Country\Resources\City::class)
                ->showOnPreview(),

            Text::make(__("Address"), 'address')
                ->showOnPreview(),

            Text::make(__("Postal Code"), 'postal_code')
                ->showOnPreview()
                ->sortable(),

            Boolean::make(__("I am the recipient of my order"), 'my_address')
                ->showOnPreview(),

            Text::make(__("Phone"), 'phone')
                ->showOnPreview()
                ->hideFromIndex(),

            Text::make(__("Recipient Name"), 'recipient_name')
                ->showOnPreview()
                ->canSee(fn() => ! $this->my_address)
                ->hideFromIndex(),   

            Text::make(__("Recipient Family"), 'recipient_family')
                ->showOnPreview()
                ->canSee(fn() => ! $this->my_address)
                ->hideFromIndex(),    

            Text::make(__("Recipient Phone Number"), 'recipient_phone_number')
                ->showOnPreview()
                ->canSee(fn() => ! $this->my_address)
                ->hideFromIndex(),
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