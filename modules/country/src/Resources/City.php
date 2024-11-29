<?php

namespace App\Country\Resources;

use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\{ID, BelongsTo, Text};
use App\System\NovaResource as Resource;

class City extends Resource
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
        return __("Cities");
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __("City");
    }

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Country\Entities\City';

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
    public static $permission = 'cities';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = ['name', 'slug', 'province.name'];

    /**
     * The relationships that should be eager loaded when performing an index query.
     *
     * @var array
     */
    public static $with = ['province'];

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

            BelongsTo::make(__("Province"), 'province', Province::class)
                ->readonly(),

            Text::make(__("Name"), 'name')
                ->rules('required'),

            Text::make(__("Unique Name"), 'slug')
                ->rules('required')
                ->creationRules('unique:provinces,slug')
                ->updateRules('unique:provinces,slug,{{resourceId}}'),
        ];
    }
}