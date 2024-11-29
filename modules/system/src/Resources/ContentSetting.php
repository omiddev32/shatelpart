<?php

namespace App\System\Resources;

use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\{ID, Text, Boolean, Image};
use App\System\NovaResource as Resource;

class ContentSetting extends Resource
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
        return __("Content Settings");
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __("Content Setting");
    }

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\System\Entities\ContentSetting';

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
    public static $permission = 'contentSettings';

    /**
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

    /**
     * Indicates if the resource should be searchable on the index view.
     *
     * @var bool
     */
    public static $searchable = false;

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            // ID::make(__('ID'),'id')
            //     ->sortable()
            //     ->onlyOnIndex(),

            Text::make(__("Name"), 'name')
                ->rules('required'),

            Text::make(__("Slug"), 'slug')
                ->rules('required'),

            Image::make(__("Image"), 'image'),

            Boolean::make(__("Button under the image"), 'has_button'),

            Text::make(__("Button text"), 'button_link')
                ->hideFromIndex(),

            Text::make(__("Link button"), 'button_text')
                ->hideFromIndex(),
        ];
    }
}