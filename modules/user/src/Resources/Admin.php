<?php

namespace App\User\Resources;

use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\{ID, Text, Password, BelongsTo, BelongsToMany, HasMany, MorphMany, PasswordConfirmation, Image};
use App\Fields\SelectPlus\SelectPlus;
use App\System\NovaResource;

class Admin extends NovaResource
{
    /**
     * Get the logical group associated with the resource.
     *
     * @return string
     */
    public static function group()
    {
        return __("Admins and Access");
    }
    
    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __("System Administrators");
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __("System Manager");
    }

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\User\Entities\Admin';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'full_name';

    /**
     * Determine permission access this resource
     *
     * @var string
     */
    public static $permission = 'admins';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = ['first_name', 'last_name', 'username', 'email', 'code', 'phone_number'];

    /**
     * The relationships that should be eager loaded when performing an index query.
     *
     * @var array
     */
    public static $with = ['organization', 'roles', 'authentications', 'logs'];

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

            Image::make(__('Profile Picture'), 'image')
                ->showOnPreview()
                ->thumbnail(function () use($request){
                    $routeName = $request->route()->getName();
                    $routeUri = $request->route()->uri;
                    if ((! $this->image) && ($routeName !== 'nova.pages.create' && $routeName !== 'nova.pages.edit' && $routeUri !== 'nova-api/{resource}/creation-fields' && $routeUri !== 'nova-api/{resource}/{resourceId}/update-fields')) {
                        return '/images/support-logo.png';
                    }
                    return "/storage/admins/{$this->image}";
                })
                ->disk('admins'),

            Text::make(__("First Name"), 'first_name'),

            Text::make(__("Last Name"), 'last_name'),

            Text::make(__("Username"), 'username')
                ->rules('required')
                ->creationRules('unique:admins,email')
                ->updateRules('unique:admins,email,{{resourceId}}'),

            Text::make(__("National Code"), 'code')
                ->rules('required')
                ->creationRules('unique:admins,code')
                ->updateRules('unique:admins,code,{{resourceId}}'),

            Text::make(__("Email"), 'email')
                ->rules('required')
                ->creationRules('unique:admins,email')
                ->updateRules('unique:admins,email,{{resourceId}}'),

            Text::make(__("Phone Number"), 'phone_number')
                ->rules('required')
                ->creationRules('unique:admins,phone_number')
                ->updateRules('unique:admins,phone_number,{{resourceId}}'),

            BelongsTo::make(__("Organizational Department") , 'organization' , Organization::class)
                ->nullable(),

            SelectPlus::make(__("Roles"), 'roles', Role::class)
                ->onlyOnForms(),

            Password::make(__('Password'),'password')
                ->onlyOnForms()
                ->creationRules('required', 'string', 'min:6')
                ->updateRules('nullable', 'string', 'min:6'),

            PasswordConfirmation::make(__('Password Confirmation') , 'password_confirmation')
                ->creationRules('required',)
                ->updateRules('nullable'),

            $this->createdAt(),

            $this->status(),

            BelongsToMany::make(__("Roles"), 'roles', Role::class)
                ->searchable(),

            // HasMany::make(__('Tracked transactions'), 'transactions', \App\Payment\Resources\Transaction::class),

            MorphMany::make(__('Authentication Logs'), 'authentications', AuthenticationLog::class),

            HasMany::make(__('Action Logs'), 'logs', \App\System\Resources\Log::class),
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return $this->statusActions();
    }
}