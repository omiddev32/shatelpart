<?php

namespace App\User\Resources;

use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\{ID, Text, HasMany, Stack, Boolean, Image, Gravatar, HasOne};
use App\System\NovaResource;
use App\Fields\PersianNumber\PersianNumber;
use Illuminate\Database\Eloquent\Model;

class User extends NovaResource
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
     * Get the displayable label of the menu.
     *
     * @return string
     */
    public static function menuTitle()
    {
        return __("Users List");
    }

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __("Users");
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __("User");
    }

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\User\Entities\User';

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
    public static $permission = 'users';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = ['first_name', 'last_name', 'email', 'code', 'phone_number'];

    /**
     * The relationships that should be eager loaded when performing an index query.
     *
     * @var array
     */
    public static $with = ['addresses', 'bankAccounts', 'transactions', 'userLegal'];

    /**
     * Register a callback to be called after the resource is created.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public static function afterCreate(NovaRequest $request, Model $model)
    {
        $model->update([
            'register_datetime' => now()
        ]);
    }

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
                ->showOnPreview()
                ->onlyOnIndex(),

           // Image::make(__('Profile Picture'), 'profile_picture')
           //      ->exceptOnForms(),

            Image::make(__('Profile Picture'), 'profile_picture')
                ->showOnPreview()
                ->thumbnail(function () use($request){
                    $routeName = $request->route()->getName();
                    $routeUri = $request->route()->uri;
                    if ((! $this->profile_picture) && ($routeName !== 'nova.pages.create' && $routeName !== 'nova.pages.edit' && $routeUri !== 'nova-api/{resource}/creation-fields' && $routeUri !== 'nova-api/{resource}/{resourceId}/update-fields')) {
                        return '/images/user-logo.png';
                    } else if(($routeName == 'nova.pages.create' || $routeName == 'nova.pages.edit' || $routeUri == 'nova-api/{resource}/creation-fields' || $routeUri == 'nova-api/{resource}/{resourceId}/update-fields')) {
                        return '';
                    }
                    return "/storage/users/{$this->profile_picture}";
                })
                ->disk('users'),

            Text::make(__("First Name"), 'first_name')
                ->rules('required')
                ->showOnPreview()
                ->sortable(),

            Text::make(__("Last Name"), 'last_name')
                ->showOnPreview()
                ->sortable(),

            Text::make(__("National Code"), 'code')
                ->showOnPreview()
                ->rules('required')
                ->sortable()
                ->creationRules('unique:users,code')
                ->updateRules('unique:users,code,{{resourceId}}'),

            Text::make(__("Email"), 'email')
                ->showOnPreview()
                ->rules('required')
                ->sortable()
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}'),

            Text::make(__("Phone Number"), 'phone_number')
                ->showOnPreview()
                ->rules('required')
                ->sortable()
                ->creationRules('unique:users,phone_number')
                ->updateRules('unique:users,phone_number,{{resourceId}}'),

            Text::make(__("Credit Amount") , function() {
                return number_format($this->model()->wallet_balance / 10) . " " . __("Toman");
            })
            ->exceptOnForms()
            ->showOnPreview(),

            Text::make(__("Withdrawable credit") , function() {
                return number_format($this->model()->withdrawable_credit / 10) . " " . __("Toman");
            })
            ->showOnPreview()
            ->exceptOnForms(),

            Text::make(__("Purchase amount so far"), function() {
                return number_format($this->model()->purchase_amount / 10) . " " . __("Toman");
            })
            ->showOnPreview()
            ->exceptOnForms(),

            $this->createdAt()->showOnPreview(),

            $this->updatedAt()
                ->showOnPreview()
                ->hideFromIndex(),

            $this->status()->exceptOnForms(),

            Boolean::make(__("Complete registration"), function() {
                return $this->register_datetime ? true : false;
            }),

            HasMany::make(__('Transactions'), 'transactions', \App\Payment\Resources\Transaction::class),

            HasOne::make(__('Legal Information'), 'userLegal', UserLegal::class),

            HasMany::make(__('Addresses'), 'addresses', UserAddress::class),

            HasMany::make(__('Bank Accounts'), 'bankAccounts', UserBankAccount::class),
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
        return array_merge($this->statusActions(), [

            (new \App\Ticket\Actions\CreateTicket)
                ->canSee(fn() => auth()->user()->hasPermission('view.any.tickets'))
                ->canRun(fn() => auth()->user()->hasPermission('view.any.tickets'))
                ->showInline()
                ->withName(__('Create') . ' ' .__('Ticket'))
                // ->confirmText(__('Credit increase or decrease'))
                ->confirmButtonText(__('Create'))
                ->cancelButtonText(__("Cancel")),

            (new \App\Wallet\Actions\CreditAction)
                ->canSee(fn() => auth()->user()->hasPermission('accreditation.users'))
                ->canRun(fn() => auth()->user()->hasPermission('accreditation.users'))
                ->showInline()
                ->withName(__('Credit increase or decrease'))
                ->confirmText(__('Credit increase or decrease'))
                ->confirmButtonText(__('Apply Credit'))
                ->cancelButtonText(__("Cancel")),
        ]);
    }
}