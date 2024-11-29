<?php

namespace App\Country\Resources;

use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\{ID, Text, Image, BelongsToMany};
use App\System\NovaResource as Resource;

class Zone extends Resource
{
    /**
     * Get the logical group associated with the resource.
     *
     * @return string
     */
    public static function group()
    {
        return __("General Settings");
    }

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __("Zones");
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __("Zone");
    }

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Country\Entities\Zone';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * Determine permission access this resource
     *
     * @var string
     */
    public static $permission = 'zones';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = ['name', 'symbol', 'symbol_2'];

    /**
     * The relationships that should be eager loaded when performing an index query.
     *
     * @var array
     */
    public static $with = ['countries'];

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

            Text::make(__('Name'), 'name')
                ->translatable()
                ->showOnPreview()
                ->rulesFor(config('app.locale'), [
                    'required',
                ]),

            Text::make(__("Symbol"), 'symbol')
                ->sortable()
                ->showOnPreview()
                ->rules('required')
                ->creationRules('unique:countries,symbol')
                ->updateRules('unique:countries,symbol,{{resourceId}}'),

            Text::make(__("Symbol") . ' 2', 'symbol_2')
                ->sortable()
                ->showOnPreview()
                ->rules('required')
                ->creationRules('unique:countries,symbol_2')
                ->updateRules('unique:countries,symbol_2,{{resourceId}}'),

            Image::make(__("Image"), 'image')
                ->showOnPreview()
                ->disk('zones'),

            BelongsToMany::make(__("Countries"), 'countries', Country::class)
                ->canSee(fn() => $this->id > 2),

        ];
    }


    /**
     * Determine if the current user can delete resources.
     *
     * @param \App\Core\Http\Request $request
     *
     * @return bool
     */
    public function authorizedToDelete(Request $request)
    {
        if ($request->user()->hasPermission('delete.' . static::$permission)) {
            return $request->user()->hasPermission('delete.' . static::$permission) && $this->id > 2;
        }
        return false;
    }
}