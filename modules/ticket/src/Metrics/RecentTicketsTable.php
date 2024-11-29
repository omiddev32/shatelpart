<?php

namespace App\Ticket\Metrics;

use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\MetricTableRow;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Metrics\Table;
use App\Ticket\Entities\Ticket;
use App\Ticket\Enums\StatusNameEnum;

class RecentTicketsTable extends Table
{

    /**
     * The width of the card (1/3, 2/3, 1/2, 1/4, 3/4, or full).
     *
     * @var string
     */
    public $width = '1/2';

    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name()
    {
        return __("Recent Tickets");
    }

    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $tickets = [];
        $user = auth()->user();
        $query = Ticket::query()->with(['user', 'ticketCategory', 'ticketCategoryTopic', 'ticketStatus']);

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

        foreach($query->latest()->take(5)->get() as $ticket) {

            $topic = $ticket->ticketCategoryTopic ? $ticket->ticketCategoryTopic->title : $ticket->subject;

            $tickets[] = MetricTableRow::make()
                ->icon('check-circle')
                ->iconClass($this->ticketColor($ticket->ticketStatus->name))
                ->title("{$ticket->ticketCategory->title} ( {$topic} )")
                ->subtitle(__("Status") . ' - ' . StatusNameEnum::instanceFromKey($ticket->ticketStatus->name)->value())
                ->actions(function() use($ticket){
                    return [
                        MenuItem::make(
                            __("Details"),
                            "/resources/tickets/{$ticket->id}"
                        )
                    ];
                });
        }

        return $tickets;
    }

    private function ticketColor($status)
    {
        $color = '';
        switch ($status) {

            case StatusNameEnum::CLOSED_BY_CUSTOMER:
                $color = 'text-gray-500';
                break;
            
            case StatusNameEnum::CLOSED_BY_ADMIN:
                $color = 'text-gray-500';
                break;
            
            case StatusNameEnum::CLOSED_AUTOMATICALLY:
                $color = 'text-gray-500';
                break;            

            case StatusNameEnum::ANSWERED:
                $color = 'text-green-500';
                break;

            case StatusNameEnum::WATING:
                $color = 'text-blue-500';
                break;

            case StatusNameEnum::OPEN:
                $color = 'text-blue-500';
                break;
        }

        return $color;

    }
}