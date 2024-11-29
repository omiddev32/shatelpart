<?php

namespace App\Country\Resources;

use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\{ID, Text, Number, BelongsToMany, Image};
use App\System\NovaResource as Resource;
use App\Fields\Translatable\HandlesTranslatable;

class Country extends Resource
{
    use HandlesTranslatable;

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
        return __("Countries");
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __("Country");
    }

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Country\Entities\Country';

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
    public static $permission = 'countries';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = ['id', 'name', 'symbol', 'symbol_2'];

    /**
     * The relationships that should be eager loaded when performing an index query.
     *
     * @var array
     */
    public static $with = ['products'];

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
                ->onlyOnDetail()
                ->showOnPreview()
                ->disk('countries.1x1'),

            $this->status()->exceptOnForms()->showOnPreview(),
            
            // BelongsToMany::make(__("Products"), 'products', \App\Product\Resources\Product::class),
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
     * Determine if the user can attach any models of the given type to the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model|string  $model
     * @return bool
     */
    public function authorizedToAttachAny(NovaRequest $request, $model)
    {
        return false;
    }

    /**
     * Determine if the user can attach models of the given type to the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model|string  $model
     * @return bool
     */
    public function authorizedToAttach(NovaRequest $request, $model)
    {
        return false;
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