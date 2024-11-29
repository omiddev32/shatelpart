<?php

namespace App\Country\Resources;

use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\{ID, Text, HasMany};
use App\System\NovaResource as Resource;

class Province extends Resource
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
        return __("Provinces");
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __("Province");
    }

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Country\Entities\Province';

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
    public static $permission = 'provinces';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = ['name', 'slug'];

    /**
     * The relationships that should be eager loaded when performing an index query.
     *
     * @var array
     */
    public static $with = ['cities'];

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

            Text::make(__("Name"), 'name')
                ->rules('required'),

            Text::make(__("Unique Name"), 'slug')
                ->rules('required')
                ->creationRules('unique:provinces,slug')
                ->updateRules('unique:provinces,slug,{{resourceId}}'),

            HasMany::make(__("Cities"), 'cities', City::class),
        ];
    }
}