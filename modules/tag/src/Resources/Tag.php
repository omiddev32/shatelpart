<?php

namespace App\Tag\Resources;

use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\{ID, Text};
use App\System\NovaResource as Resource;

class Tag extends Resource
{
    /**
     * Get the logical group associated with the resource.
     *
     * @return string
     */
    public static function group()
    {
        return __("Shop");
    }

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __("Tags");
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __("Tag");
    }

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Tag\Entities\Tag';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    /**
     * Determine permission access this resource
     *
     * @var string
     */
    public static $permission = 'tags';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = ['title'];

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

            Text::make(__("Title"), 'title')
                ->sortable()
                ->rules('required')
                ->creationRules('unique:tags,title')
                ->updateRules('unique:tags,title,{{resourceId}}'),

            $this->status()->exceptOnForms(),
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