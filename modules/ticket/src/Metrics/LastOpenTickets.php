<?php

namespace App\Ticket\Metrics;

use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Table;
use App\Ticket\Entities\Ticket;
use Laravel\Nova\Metrics\MetricTableRow;

class LastOpenTickets extends Table
{

    /**
     * The width of the card (1/3, 2/3, 1/2, 1/4, 3/4, or full).
     *
     * @var string
     */
    public $width = 'full';

    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $rows = [];

        $tickets = Ticket::with('user', 'ticketStatus:id,name')->whereNotIn('ticket_status_id', [4,5,6])->get();

        return [
            MetricTableRow::make()
                ->title('v1.0')
                ->subtitle('Initial release of Laravel Nova'),

            MetricTableRow::make()
                ->title('v2.0')
                ->subtitle('The second major series of Laravel Nova'),
        ];
    }
}