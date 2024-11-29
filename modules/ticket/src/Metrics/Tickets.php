<?php

namespace App\Ticket\Metrics;

use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;
use App\Ticket\Entities\TicketStatus;
use App\Ticket\Enums\StatusNameEnum;

class Tickets extends Partition
{
    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name()
    {
        return __("Tickets Status");
    }

    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $statuses = [];
        $closedTickets = 0;

        foreach(TicketStatus::select(['id', 'name'])->withCount('tickets')->where('status', true)->get() as $status) {
            if($status->id <= 6) {
                if(
                    $status->name === StatusNameEnum::CLOSED_BY_ADMIN ||
                    $status->name === StatusNameEnum::CLOSED_BY_CUSTOMER ||
                    $status->name === StatusNameEnum::CLOSED_AUTOMATICALLY
                ) {

                    if($status->tickets_count > 0) {
                        $closedTickets += $status->tickets_count;
                    }

                } else {
                    $statuses[StatusNameEnum::instanceFromKey($status->name)->value()] = $status->tickets_count;
                }

            } else {
                $statuses[$status->name] = $status->tickets_count;
            }
        }

        $statuses[__("Closed")] = $closedTickets;

        return $this->result($statuses);
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return  \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        // return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'tickets';
    }
}