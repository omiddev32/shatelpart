<?php

namespace App\Question\Resources;

use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\{ID, Text, BelongsToMany};
use App\System\NovaResource as Resource;
use App\Fields\Translatable\HandlesTranslatable;

class Question extends Resource
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
        return __("List of questions");
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __("Question");
    }

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Question\Entities\Question';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'question';

    /**
     * Determine permission access this resource
     *
     * @var string
     */
    public static $permission = 'questions';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = ['question', 'answer'];

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

            Text::make(__("Question"), 'question')
                ->translatable()
                // ->rules('required')
                ->rulesFor(config('app.locale'), [
                    'required',
                ])
                ->sortable(),

            Text::make(__("Answer"), 'answer')
                ->hideFromIndex()
                ->translatable()
                // ->rules('required')
                ->rulesFor(config('app.locale'), [
                    'required',
                ])
                ->sortable(),

            BelongsToMany::make(__("Products"), 'products', \App\Product\Resources\Product::class)
                ->searchable(),

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