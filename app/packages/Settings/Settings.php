<?php

namespace App\Packages\Settings;

use Laravel\Nova\Menu\{Breadcrumbs, Breadcrumb};
use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\{Nova, Tool};
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Settings extends Tool
{    
    /**
     * Get the displayable singular label of the tool.
     *
     * @return string
     */
    public function label()
    {
        return Str::singular(Str::title(Str::snake(class_basename(get_called_class()), ' ')));
    }

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public $model = null;

    /**
     * If you are considering a specific row of a table, write the desired key to search.
     *
     * @var string
     */
    public $primaryKey = 'id';

    /**
     * If you are considering a specific row of the table, write the desired title to search.
     *
     * @var string
     */
    public $primaryValue = 1;

    /**
     * The relationships that should be eager loaded when performing an index query.
     *
     * @var array
     */
    public $with = [];

    /**
     * Determine permission access this resource
     *
     * @var string
     */
    public $permission = null;

    /**
     * Create a new Tool.
     *
     * @return void
     */
    public function __construct()
    {
        // 
    }

    /**
     * Perform any tasks that need to happen when the tool is booted.
     *
     * @return void
     */
    public function boot()
    {
        $this->seeCallback = fn() => $this->authorizedToUpdate(request());
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [];
    }

    /**
     * Get the text for the save settings button.
     *
     * @return string|null
     */
    public function saveButtonLabel()
    {
        return __('Save :resource', ['resource' => $this->label()]);
    }

    /**
     * Get the text for the save settings.
     *
     * @return string|null
     */
    public function saveMessage()
    {
        return __('Settings saved.');
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
            return MenuSection::make($this->label())
                ->path('/settings/'. $this->uriKey())
                ->icon($this->icon());
        }
    }

    /**
     * Register a callback to be called befor the resource is save.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public static function beforSave(NovaRequest $request, Model $model)
    {
        //
    }

    /**
     * Register a callback to be called after the resource is save.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public static function afterSave(NovaRequest $request, Model $model)
    {
        //
    }

    /**
     * If you are considering a specific row of a table, write the desired key to search.
     *
     * @var string
     */
    public function primaryKey()
    {
        return $this->primaryKey;
    }
    
    /**
     * If you are considering a specific row of the table, write the desired title to search.
     *
     * @var string
     */
    public function primaryValue()
    {
        return $this->primaryValue;
    }

    /**
     * Get breadcrumb menu for the page.
     *
     * @param  Request  $request
     * @return \Laravel\Nova\Menu\Breadcrumbs
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function breadcrumbs(Request $request)
    {
        return Breadcrumbs::make([
            Breadcrumb::make(__('Dashboard'))->path('/dashboards/main'),
            Breadcrumb::make($this->label()),
        ]);
    }

    /**
     * Determine if the current user can update the given resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function authorizedToUpdate(Request $request)
    {
        return $request->user()?->hasPermission('update.' . $this->permission) ?? false;
    }

    /**
     * Get the icon for the navigation.
     *
     * @return string
     */
    public function icon($icon = 'adjustments')
    {
        return $icon;
    }

    /**
     * Get the URI key for the tool.
     *
     * @return string
     */
    public static function uriKey($uri = '')
    {
        return $uri ?: Str::plural(Str::kebab(class_basename(get_called_class())));
    }
}
