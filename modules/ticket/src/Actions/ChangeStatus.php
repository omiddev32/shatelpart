<?php

namespace App\Ticket\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\{ActionFields, Select};
use App\Ticket\Enums\StatusNameEnum;

class ChangeStatus extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields $fields
     * @param  \Illuminate\Support\Collection $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        foreach ($models as $model) :
            $model->ticket_status_id = $fields->ticketStatus;
            $model->update(); 
        endforeach;
    }

    /**
     * Get the fields available on the action.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Select::make(__("Status"), 'ticketStatus')
                ->options(function() {
                    $statuses = [];
                    foreach(\App\Ticket\Entities\TicketStatus::select(['id', 'name'])->whereNotIn('name', [
                        StatusNameEnum::OPEN,
                        StatusNameEnum::WATING,
                        StatusNameEnum::CLOSED_BY_ADMIN,
                        StatusNameEnum::CLOSED_BY_CUSTOMER,
                        StatusNameEnum::CLOSED_AUTOMATICALLY,
                    ])->get() as $status) {
                        $title = $status->name;
                        if($status->id < 7) {
                            $title = StatusNameEnum::instanceFromKey($status->name)->value();
                        }
                        $statuses[$status->id] = $title;
                    }
                    return $statuses;
                })
                ->rules('required'),
        ];
    }
}