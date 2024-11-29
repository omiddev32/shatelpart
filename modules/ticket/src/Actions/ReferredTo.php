<?php

namespace App\Ticket\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\{ActionFields, Text, Heading, Select, FormData, Textarea};
use App\Ticket\Entities\Ticket;

class ReferredTo extends Action
{
    use InteractsWithQueue, Queueable;

    private $data;

    /**
     * The size of the modal. Can be "sm", "md", "lg", "xl", "2xl", "3xl", "4xl", "5xl", "6xl", "7xl".
     *
     * @var string
     */
    public $modalSize = '4xl';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields $fields
     * @param  \Illuminate\Support\Collection $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $ticket = $models->first();

        $message = new \App\Ticket\Entities\TicketMessage;
        $message->ticket_id = $ticket->id;
        $message->text = $fields->description;
        $message->type = 'referred';
        $message->referred_from_admin = auth()->user()->id;
        $message->referred_to_admin = $fields->admin;
        $message->modelable()->associate(auth()->user());
        $message->save();
        
        $ticket->last_referred_to_admin = $fields->admin; 
        $ticket->last_referred_at = now();
        $ticket->update();
    }

    public function data($data) 
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Get the fields available on the action.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        $ticketNumberLabel = __("Ticket Number");
        $subjectLabel = __("Subject");
        $subjectValue = $this->data->ticketCategoryTopic ? $this->data?->ticketCategoryTopic->title : $this->data?->subject;
        $currentReferenceLabel = __("Current reference");
        $currentReferenceLabelValue = $this->data?->referredTo ? $this->data?->referredTo?->full_name : $this->data?->firstReferredTo?->full_name;

        return [

            Heading::make("

                <div 
                    class='flex flex-col -mx-6 px-6 py-2 space-y-2 md:flex-row @sm/peekable:flex-row @md/modal:flex-row md:py-0 @sm/peekable:py-0 @md/modal:py-0 md:space-y-0 @sm/peekable:space-y-0 @md/modal:space-y-0'
                 >
                    <div class='md:w-1/4 @sm/peekable:w-1/4 @md/modal:w-1/4 md:py-3 @sm/peekable:py-3 @md/modal:py-3'>

                        <h4 class='font-normal @sm/peekable:break-all'>
                            <span>{$ticketNumberLabel}</span>
                        </h4>
                    </div>
                    <div class='break-all md:w-3/4 @sm/peekable:w-3/4 @md/modal:w-3/4 md:py-3 @sm/peekable:py-3 md/modal:py-3 lg:break-words @md/peekable:break-words @lg/modal:break-words'>
                        <p class='flex items-center'>
                            {$this->data?->ticket_number}
                        </p>
                    </div>
                </div>
                
                <div 
                    class='flex flex-col -mx-6 px-6 py-2 space-y-2 md:flex-row @sm/peekable:flex-row @md/modal:flex-row md:py-0 @sm/peekable:py-0 @md/modal:py-0 md:space-y-0 @sm/peekable:space-y-0 @md/modal:space-y-0'
                 >
                    <div class='md:w-1/4 @sm/peekable:w-1/4 @md/modal:w-1/4 md:py-3 @sm/peekable:py-3 @md/modal:py-3'>

                        <h4 class='font-normal @sm/peekable:break-all'>
                            <span>{$subjectLabel}</span>
                        </h4>
                    </div>
                    <div class='break-all md:w-3/4 @sm/peekable:w-3/4 @md/modal:w-3/4 md:py-3 @sm/peekable:py-3 md/modal:py-3 lg:break-words @md/peekable:break-words @lg/modal:break-words'>
                        <p class='flex items-center'>
                            {$subjectValue}
                        </p>
                    </div>
                </div>
                
                <div 
                    class='flex flex-col -mx-6 px-6 py-2 space-y-2 md:flex-row @sm/peekable:flex-row @md/modal:flex-row md:py-0 @sm/peekable:py-0 @md/modal:py-0 md:space-y-0 @sm/peekable:space-y-0 @md/modal:space-y-0'
                 >
                    <div class='md:w-1/4 @sm/peekable:w-1/4 @md/modal:w-1/4 md:py-3 @sm/peekable:py-3 @md/modal:py-3'>

                        <h4 class='font-normal @sm/peekable:break-all'>
                            <span>{$currentReferenceLabel}</span>
                        </h4>
                    </div>
                    <div class='break-all md:w-3/4 @sm/peekable:w-3/4 @md/modal:w-3/4 md:py-3 @sm/peekable:py-3 md/modal:py-3 lg:break-words @md/peekable:break-words @lg/modal:break-words'>
                        <p class='flex items-center'>
                            {$currentReferenceLabelValue}
                        </p>
                    </div>
                </div>

            ")->asHtml(),

            Select::make(__("Organization"), 'organization')
                ->rules('required')
                ->options(function() {
                    $list = [];
                    foreach(\App\User\Entities\Organization::select(['id', 'name'])->get() as $org) {
                        $list[$org->id] = $org->name;
                    }
                    return $list;
                }),

            Select::make(__("Referral default user"), 'admin')
                ->rules('required')
                ->readonly()
                ->options([])
                ->dependsOn('organization', function (Select $field, NovaRequest $request, FormData $formData) {
                    if ($formData->organization) {
                        $field->readonly(false);
                        $adminUsers = [];
                        $organization = \App\User\Entities\Organization::with(['admin', 'admins'])->find($formData->organization);
                        $ticket = Ticket::find(request()->resources[0]);
                        $exceptId = null;

                        if($ticket) {
                            $exceptId = $ticket->last_referred_to_admin ?: $ticket->first_referred_to_admin;
                        }

                        if($organization->admin && $organization->admin->id !== $exceptId) {
                            $adminUsers[$organization->admin->id] = $organization->admin->full_name;
                        }
                        if($organization->admins->count()) {
                            foreach($organization->admins as $admin) {

                                if($admin->id != $exceptId) {
                                    $adminUsers[$admin->id] = $admin->full_name;
                                }

                            }
                        }
                        $field->options($adminUsers);
                    }
                }),

            Textarea::make(__("Description"), 'description'),
                
        ];
    }

    /**
     * Get the URI key for the action.
     *
     * @return string
     */
    public function uriKey()
    {
        return "referred-to-action";
    }
}