<?php

namespace App\Ticket\Resources;

use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\{ID, Text, Boolean, Textarea, Select, HasMany};
use App\System\NovaResource as Resource;
use App\Fields\Translatable\HandlesTranslatable;
use App\Ticket\Enums\StatusNameEnum;

class TicketStatus extends Resource
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
        return __("Statuses");
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __("Status");
    }

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Ticket\Entities\TicketStatus';

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title()
    {
        if($this->id > 7) {
            return $this->name;
        }

        return StatusNameEnum::instanceFromKey($this->name)->value();
    }

    /**
     * Determine permission access this resource
     *
     * @var string
     */
    public static $permission = 'ticketStatuses';

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
     * The relationships that should be eager loaded when performing an index query.
     *
     * @var array
     */
    public static $with = ['tickets'];

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

            Select::make(__("Name"), 'name')
                ->displayUsingLabels()
                ->onlyOnUpdating()
                ->readonly($this->id < 8)
                ->canSee(fn() => $this->id < 8)
                ->options(StatusNameEnum::map()),

            Text::make(__("Name"), 'name')
                ->rules('required')
                ->onlyOnForms()
                ->canSee(fn() => $this->id == null || $this->id > 7),

            Text::make(__("Name"), function() {
                return $this->id > 7 ? $this->name : StatusNameEnum::instanceFromKey($this->name)->value();
            })
            ->exceptOnForms(),

            Text::make(__('Display text to the customer'), 'display_name')
                ->translatable()
                ->rulesFor(config('app.locale'), [
                    'required',
                ]),

            Boolean::make(__("Notification to interested admins should be active"), 'send_notification_to_admin'),

            Textarea::make(__('Admin notification text'), 'notification_text')
                ->translatable(),

            Text::make(__("Number of open tickets"), function() {
                return $this->tickets->whereNotIn('name', [
                    StatusNameEnum::CLOSED_BY_ADMIN,
                    StatusNameEnum::CLOSED_BY_CUSTOMER,
                    StatusNameEnum::CLOSED_AUTOMATICALLY,
                ])->count();
            })
            ->hideFromIndex()
            ->showOnPreview(),
            // ->onlyOnIndex(),

            $this->status()->readonly($this->id != null && $this->id < 7),

            HasMany::make(__("Tickets"), 'tickets', Ticket::class),
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
        if($this->id < 8) return true;

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
        return $request->user()?->hasPermission('delete.' . static::$permission) && $this->id > 7 && $this->model()->tickets->count() == 0 ?? false;
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return $this->statusActions([1,2,3,4,5,6,7]);
    }
}