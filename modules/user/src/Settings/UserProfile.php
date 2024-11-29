<?php

namespace App\User\Settings;

use App\Packages\Settings\Settings;
use Laravel\Nova\Fields\{Text, BelongsTo, Password, PasswordConfirmation, Select, Image};
use Laravel\Nova\Menu\MenuItem;
use Illuminate\Http\Request;

class UserProfile extends Settings
{
    /**
     * Get the displayable singular label of the tool.
     *
     * @return string
     */
    public function label()
    {
        return __("Profile");
    }

    /**
     * The model the settings corresponds to.
     *
     * @var string
     */
    public $model = \App\User\Entities\Admin::class;

    /**
     * Determine permission access this resource
     *
     * @var string
     */
    public $permission = 'view.dashboard.admin';

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
    	return [
            Image::make(__('Profile Picture'), 'image')
                ->disk('admins'),

            Text::make(__("First Name"), 'first_name'),

            Text::make(__("Last Name"), 'last_name'),

            Text::make(__("Username"), 'username')
                ->rules('required')
                ->creationRules('unique:admins,username')
                ->updateRules('unique:admins,username,'. $this->primaryValue()),

            Text::make(__("National Code"), 'code')
                ->rules('required')
                ->creationRules('unique:admins,code')
                ->updateRules('unique:admins,code,'. $this->primaryValue()),

            Text::make(__("Email"), 'email')
                ->rules('required')
                ->creationRules('unique:admins,email')
                ->updateRules('unique:admins,email,'. $this->primaryValue()),

            Text::make(__("Phone Number"), 'phone_number')
                ->rules('required')
                ->creationRules('unique:admins,phone_number')
                ->updateRules('unique:admins,phone_number,'. $this->primaryValue()),

            // BelongsTo::make(__("Organizational Department") , 'organization' , \App\User\Resources\Organization::class)
            //     ->nullable(),

            Select::make(__("Organizational Department"), 'organization_id')
                ->nullable()
                ->canSee(fn() => $request->user()->hasPermission('admin.admins'))
                ->options(function() {
                    $list = [];
                    foreach(\App\User\Entities\Organization::select('id', 'name')->get() as $org) {
                        $list[$org->id] = $org->name;
                    }
                    return $list;
                }),

            Password::make(__('Password'),'password')
                ->onlyOnForms()
                ->creationRules('required', 'string', 'min:6')
                ->updateRules('nullable', 'string', 'min:6'),

            PasswordConfirmation::make(__('Password Confirmation') , 'password_confirmation')
                ->creationRules('required',)
                ->updateRules('nullable'),

        ];
    }

    /**
     * Determine if the current user can update the given resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function authorizedToUpdate(Request $request)
    {
        return $request->user()?->hasPermission($this->permission) ?? false;
    }

    /**
     * Get the text for the save settings button.
     *
     * @return string|null
     */
    public function saveButtonLabel()
    {
        return __('Edit :resource', ['resource' => $this->label()]);
    }

    /**
     * Get the text for the save settings.
     *
     * @return string|null
     */
    public function saveMessage()
    {
        return __('Profile edited successfully.');
    }

    /**
     * If you are considering a specific row of the table, write the desired title to search.
     *
     * @var string
     */
    public function primaryValue()
    {
        return auth()->user()->id;
    }

    /**
     * Build the menu that renders the navigation links for the tool.
     *
     * @param  \Illuminate\Http\Request $request
     * @return mixed
     */
    public function menu(Request $request)
    {
        if($this->seeCallback !== false) {
            return MenuItem::make($this->label())
                ->path('/settings/'. $this->uriKey())
                ->mainGroup(__("Admins and Access"));
        }
        return ;
    }
}
