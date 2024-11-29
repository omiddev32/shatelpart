<?php

namespace App\Ticket\Resources;

use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\{ID, Text, BelongsTo, FormData, HasMany, Boolean};
use Illuminate\Database\Eloquent\Builder;
use App\System\NovaResource as Resource;
use App\Fields\Translatable\HandlesTranslatable;
use App\Ticket\Enums\StatusNameEnum;

class TicketCategoryTopic extends Resource
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
        return __("Default Topics");
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __("Default Topic");
    }

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Ticket\Entities\TicketCategoryTopic';

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
    public static $permission = 'ticketCategoryTopics';

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
    public static $with = ['ticketCategory', 'ticketCategoryTopicContents', 'tickets'];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        // viaResource = ticket-categories, viaRelationship = ticketCategoryTopics, viaResourceId

        return [
            ID::make(__('ID'),'id')
                ->sortable()
                ->onlyOnIndex(),

            BelongsTo::make(__("Ticket Category") , 'ticketCategory' , TicketCategory::class)
                ->readonly(
                    fn() => $request->viaResource === "ticket-categories" && 
                    $request->viaRelationship === 'ticketCategoryTopics' &&
                    $request->viaResourceId
                ),

            Text::make(__('Subject'), 'title')
                ->translatable()
                ->rulesFor(config('app.locale'), [
                    'required',
                ]),

            BelongsTo::make(__("Referral default user") , 'admin' , \App\User\Resources\Admin::class)
                ->nullable()
                ->hide()
                ->dependsOn('ticketCategory', function (BelongsTo $field, NovaRequest $request, FormData $formData) {


                    $input = $formData->ticketCategory;

                    if (
                        $request->viaResource === "ticket-categories" && 
                        $request->viaRelationship === 'ticketCategoryTopics' &&
                        $request->viaResourceId
                    ) {
                        $input = $request->viaResourceId;
                    }

                    $category = \App\Ticket\Entities\TicketCategory::with('organizations', 'organizations.admin', 'organizations.admins')->find($input);
                    $field->show();
                    $adminUsers = [];

                    if($category?->organizations->count()) {
                        $category->organizations->map(function($org) use(& $adminUsers) {
                            if($org->admin) {
                                $adminUsers[] = $org->admin->id;
                            }
                            if($org->admins->count()) {
                                $adminUsers = array_merge($org->admins->pluck('id')->toArray(), $adminUsers);
                            }
                        });
                    }
                    $field->relatableQueryUsing(function (NovaRequest $request, Builder $query) use($adminUsers) {
                        $query->whereIn('id', array_unique($adminUsers));
                    });
                }),

            Text::make(__("Number of open tickets"), function() {
                return $this->tickets->whereNotIn('name', [
                    StatusNameEnum::CLOSED_BY_ADMIN,
                    StatusNameEnum::CLOSED_BY_CUSTOMER,
                    StatusNameEnum::CLOSED_AUTOMATICALLY,
                ])->count();
            })
            ->showOnPreview(),
            // ->onlyOnIndex(),

            Text::make(__("Total number of tickets"), function() {
                return $this->tickets->count();
            })
            ->hideFromIndex()
            ->showOnPreview(),
            // ->onlyOnIndex(),

            $this->status()->exceptOnForms(),

            HasMany::make(__("Ticket Category Topic Contents"), 'ticketCategoryTopicContents', TicketCategoryTopicContent::class),
        ];
    }

    /**
     * If there is permission to delete and the deletion is prevented for other reasons
     * 
     * @return string
     */
    public function reasonForConditionalDeletion()
    {
        return __("Due to the existence of connected tickets, it is not possible to delete!");
    }

    /**
     * Checks other conditions if delete access exists.
     *
     * @return boolean
     */
    public function conditionalDeletion($request)
    {
        return $this->model()->tickets->count() == 0;
    }

    /**
     * Determine if the current user can delete the given resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function authorizedToDelete(Request $request)
    {
        return $request->user()?->hasPermission('delete.' . static::$permission) && $this->model()->tickets->count() == 0 ?? false;
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