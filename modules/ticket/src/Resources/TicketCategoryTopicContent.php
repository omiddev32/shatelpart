<?php

namespace App\Ticket\Resources;

use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\{ID, Text, BelongsTo, URL, Boolean, Select};
use App\System\NovaResource as Resource;
use App\Fields\JsonTags\JsonTags;
use App\Fields\Translatable\HandlesTranslatable;

class TicketCategoryTopicContent extends Resource
{
    use HandlesTranslatable;

    /**
     * Get the logical group associated with the resource.
     *
     * @return string
     */
    public static function group()
    {
        return __("Ticket Management");
    }
    
    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __("Ticket Category Topic Contents");
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __("Ticket Category Topic Content");
    }

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Ticket\Entities\TicketCategoryTopicContent';

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
    public static $permission = 'ticketCategoryTopicContents';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = ['title'];


    /**
     * The relationships that should be eager loaded when performing an index query.
     *
     * @var array
     */
    public static $with = ['ticketCategoryTopic'];

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

            BelongsTo::make(__("Default Topic") , 'ticketCategoryTopic' , TicketCategoryTopic::class)
                ->readonly(
                    fn() => $request->viaResource === "ticket-category-topics" && 
                    $request->viaRelationship === 'ticketCategoryTopicContents' &&
                    $request->viaResourceId
                ),

            Text::make(__('Title'), 'title')
                ->rules('required'),

            Select::make(__("Language"), 'language')
                ->options(config('translatable.locales'))
                ->default(app()->getLocale())
                ->rules('required'),

            JsonTags::make(__("Key Words"), 'keywords'),

            URL::make(__("Content Link"), 'link'),

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