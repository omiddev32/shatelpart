<?php

namespace App\Ticket\Resources;

use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\{ID, Text, Textarea, BelongsTo, Boolean, HasMany, Heading};
use App\System\NovaResource as Resource;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Fields\FormData;
use App\Fields\SelectPlus\SelectPlus;
use App\User\Entities\Organization;
use App\Fields\Translatable\HandlesTranslatable;
use App\Ticket\Enums\StatusNameEnum;

class TicketCategory extends Resource
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
        return __("Ticket Categories");
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __("Ticket Category");
    }

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Ticket\Entities\TicketCategory';

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
    public static $permission = 'ticketCategories';

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
    public static $with = ['organizations', 'admin', 'tickets'];

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

            Text::make(__('Category Name'), 'title')
                ->translatable()
                ->rulesFor(config('app.locale'), [
                    'required',
                ]),

            Textarea::make(__('Description'), 'description')
                ->translatable()
                ->rulesFor(config('app.locale'), [
                    'required',
                ]),

            SelectPlus::make(
                __("Organizational units authorized to respond"),
                'organizations',
                \App\User\Resources\Organization::class
            )
            ->hideFromIndex()
            ->usingDetailLabel(fn() => $this->organizations?->pluck('name'))
            ->rules('required')
            ->optionsQuery(function (Builder $query) {
                $query->has('admin')->orHas('admins');
            }),

            Heading::make("<p class='text-[#1e1b4b] py-5'>". __('To choose the default referral user, please first select the organizational units authorized to respond!') ."</p>")
                ->asHtml()
                ->onlyOnForms()
                ->dependsOn('organizations', function (Heading $field, NovaRequest $request, FormData $formData) {
                    if (is_array($formData->organizations) && count($formData->organizations)) {
                        $field->hide();
                    }
                }),

            BelongsTo::make(__("Referral default user") , 'admin' , \App\User\Resources\Admin::class)
                ->hide()
                ->dependsOn('organizations', function (BelongsTo $field, NovaRequest $request, FormData $formData) {
                    if (is_array($formData->organizations) && count($formData->organizations)) {
                        $field->show();
                        $adminUsers = [];
                        Organization::with(['admin', 'admins'])->whereIn('id', collect($formData->organizations)->pluck('id')->toArray())->get()->map(function($org) use(& $adminUsers) {
                            if($org->admin) {
                                $adminUsers[] = $org->admin->id;
                            }
                            if($org->admins->count()) {
                                $adminUsers = array_merge($org->admins->pluck('id')->toArray(), $adminUsers);
                            }
                        });
                        $field->relatableQueryUsing(function (NovaRequest $request, Builder $query) use($adminUsers) {
                            $query->whereIn('id', array_unique($adminUsers));
                        });
                    }
                }),

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

            $this->status()->exceptOnForms(),

            HasMany::make(__("Tickets"), 'tickets', Ticket::class),

            HasMany::make(__("Default Topics"), 'ticketCategoryTopics', TicketCategoryTopic::class),
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