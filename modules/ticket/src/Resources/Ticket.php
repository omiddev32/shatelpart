<?php

namespace App\Ticket\Resources;

use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\{ID, Text, BelongsTo, Boolean};
use App\Ticket\Fields\Messages;
use App\System\NovaResource as Resource;
use App\Fields\PersianDate\PersianDateTime;
use App\Ticket\Enums\StatusNameEnum;
use Storage;

class Ticket extends Resource
{
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
        return __("Tickets");
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __("Ticket");
    }

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Ticket\Entities\Ticket';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'ticket_number';

    /**
     * Determine permission access this resource
     *
     * @var string
     */
    public static $permission = 'tickets';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = ['ticket_number', 'user.first_name', 'user.last_name'];

    /**
     * The relationships that should be eager loaded when performing an index query.
     *
     * @var array
     */
    public static $with = ['user', 'referredTo', 'ticketStatus', 'firstReferredTo', 'ticketCategory', 'ticketCategoryTopic'];

    /**
     * Build an "index" query for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        $user = auth()->user();
        

        if(! $user->hasPermission('admin.admins')) {

            $user->load(['organization:id', 'organizations:id']);
            $organizations = [];

            if($user->organization) {
                $organizations[] = $user->organization->id;
            }
            foreach($user?->organizations as $organization) {
                $organizations[] = $organization->id;
            }

            $query
                ->whereHas('ticketCategory.organizations', function($query) use($organizations) {
                    $query->whereIn('organizations.id', $organizations);
                })
                ->orWhere('tickets.first_referred_to_admin', $user->id)
                ->orWhere('tickets.last_referred_to_admin', $user->id);
        }

        return parent::indexQuery($request, $query);
    }

    /**
     * Build a "detail" query for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function detailQuery(NovaRequest $request, $query)
    {
        $user = auth()->user();
        
        if(! $user->hasPermission('admin.admins')) {

            $user->load(['organization:id', 'organizations:id']);
            $organizations = [];

            if($user->organization) {
                $organizations[] = $user->organization->id;
            }
            foreach($user?->organizations as $organization) {
                $organizations[] = $organization->id;
            }

            $query
                ->whereHas('ticketCategory.organizations', function($query) use($organizations) {
                    $query->whereIn('organizations.id', $organizations);
                })
                ->orWhere('tickets.first_referred_to_admin', $user->id)
                ->orWhere('tickets.last_referred_to_admin', $user->id);

        }

        return parent::detailQuery($request, $query);
    }

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

            Text::make(__("Ticket Number"), 'ticket_number')
                ->sortable(),

            BelongsTo::make(__("User"), 'user', \App\User\Resources\User::class),

            Text::make(__("Ticket Category"), function() {
                return $this->ticketCategory->title;
            }),

            Text::make(__("Subject"), function() {
                return $this->ticketCategoryTopic ? $this->ticketCategoryTopic->title : $this->subject;
            }),

            Text::make(__("Referred To"), fn() => $this->referredTo ? $this->referredTo->full_name : $this->firstReferredTo->full_name),

            // BelongsTo::make(__("Referred To"), 'referredTo', \App\User\Resources\Admin::class),

            Boolean::make(__("Critical"), 'critical'),

            BelongsTo::make(__("Status"), 'ticketStatus', TicketStatus::class),

            PersianDateTime::make(__("Created At"), 'created_at')
                ->color('rgb(30, 136, 229)')
                ->format('jYYYY/jMM/jDD HH:mm:ss'),

            Messages::make(__("Response"))
                ->withMeta([
                    'canReferral' => ! ($this->ticketStatus && ($this->ticketStatus->name == StatusNameEnum::CLOSED_BY_CUSTOMER || $this->ticketStatus->name == StatusNameEnum::CLOSED_BY_ADMIN || $this->ticketStatus->name == StatusNameEnum::CLOSED_AUTOMATICALLY)) && auth()->user()->hasPermission('referral.tickets'),
                    'canClose' => ! ($this->ticketStatus  && ($this->ticketStatus->name == StatusNameEnum::CLOSED_BY_CUSTOMER || $this->ticketStatus->name == StatusNameEnum::CLOSED_BY_ADMIN || $this->ticketStatus->name == StatusNameEnum::CLOSED_AUTOMATICALLY)) && auth()->user()->hasPermission('close.tickets')
                ])
                ->canSendMessage(function() {
                    if($this->ticketStatus && ($this->ticketStatus->name == StatusNameEnum::CLOSED_BY_CUSTOMER || $this->ticketStatus->name == StatusNameEnum::CLOSED_BY_ADMIN || $this->ticketStatus->name == StatusNameEnum::CLOSED_AUTOMATICALLY) || ! auth()->user()->hasPermission('reply.tickets')) {
                        return false;
                    }
                    return true;
                })         
                ->options(function(){
                    $this->model()->load('messages');

                    return $this->messages->map(function($message) {
                        $isAdmin = $message->modelable_type === 'App\User\Entities\Admin';
                        return [
                            'text' => $message->text,
                            'logo' => $this->getUserLogo($isAdmin, $isAdmin ? $message->modelable->image : $message->modelable->profile_picture),
                            'modelable_type' =>  $isAdmin ? 'admin' : 'user',
                            'message_type' => $message->type,
                            'referredFrom' => $message?->referredFrom?->full_name,
                            'referredTo' => $message?->referredTo?->full_name,
                            'admin' =>  $isAdmin ? $message->modelable->full_name : '',
                            'created_at' => \Morilog\Jalali\Jalalian::forge($message->created_at)->format("Y-m-d H:i:s"),
                            'files' => $message->files->map(fn($file): array => [
                                'path' => Storage::disk('tickets')->url($file->path),
                                'format' => $file->format
                            ])->toArray(),
                        ];
                    });

                }),
        ];
    }

    /**
     * Generate ticket number
     *
     * @return string
     */
    private function getUserLogo($isAdmin = false, $image = null)
    {
        return ! $image ? ($isAdmin ? '/images/support-logo.png' : '/images/user-logo.png') : Storage::disk($isAdmin ? 'admins' : 'users')->url($image);
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [
            (new \App\Ticket\Actions\ReferredTo)
                ->data($this->model())
                ->onlyOnTableRow()
                ->showOnDetail()
                ->canSee(fn() => ! ($this->ticketStatus && ($this->ticketStatus->name == StatusNameEnum::CLOSED_BY_CUSTOMER || $this->ticketStatus->name == StatusNameEnum::CLOSED_BY_ADMIN || $this->ticketStatus->name == StatusNameEnum::CLOSED_AUTOMATICALLY)) && auth()->user()->hasPermission('referral.tickets'))
                ->withName(__('Ticket referral'))
                ->confirmText('')
                ->confirmButtonText(__('Referral'))
                ->cancelButtonText(__("Cancel")),            

            (new \App\Ticket\Actions\CloseTicket)
                ->data($this->model())
                ->onlyOnTableRow()
                ->showOnDetail()
                ->canSee(fn() => ! ($this->ticketStatus  && ($this->ticketStatus->name == StatusNameEnum::CLOSED_BY_CUSTOMER || $this->ticketStatus->name == StatusNameEnum::CLOSED_BY_ADMIN || $this->ticketStatus->name == StatusNameEnum::CLOSED_AUTOMATICALLY)) && auth()->user()->hasPermission('close.tickets'))
                ->withName(__('Close Ticket')),

            (new \App\Ticket\Actions\ChangeStatus)
                ->showInline()
                ->withName(__('Change Status')),
        ];
    }
    
    /**
     * Determine if the user can run the given action.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Laravel\Nova\Actions\Action  $action
     * @return bool
     */
    public function authorizedToRunAction(NovaRequest $request, \Laravel\Nova\Actions\Action $action)
    {
        return true;
    }

    /**
     * Determine if the user can run the given action.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Laravel\Nova\Actions\DestructiveAction  $action
     * @return bool
     */
    public function authorizedToRunDestructiveAction(NovaRequest $request, \Laravel\Nova\Actions\DestructiveAction $action)
    {
        return true;
    }
}