<?php

namespace App\System\Dashboards;

use App\User\Metrics\NewUsersPerDayTrend;
use App\Product\Metrics\{ProductsPartition};
use App\Ticket\Metrics\{Tickets, LastOpenTickets, RecentTicketsTable};
use App\Order\Metrics\{OrdersPartition, OrdersPerDayTrend, RecentOrdersTable};
use Laravel\Nova\Dashboards\Main as Dashboard;

class Main extends Dashboard
{
    /**
     * Get the displayable name of the dashboard.
     *
     * @return string
     */
    public function name()
    {
        return __("Dashboard");
    }

    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards()
    {
        return [
            (new Tickets)->canSee(fn() => request()->user()->hasPermission('view.any.tickets')),
            (new OrdersPartition)->canSee(fn() => request()->user()->hasPermission('view.any.orders')),
            (new NewUsersPerDayTrend)->canSee(fn() => request()->user()->hasPermission('view.any.users')),
            (new OrdersPerDayTrend)->canSee(fn() => request()->user()->hasPermission('view.any.orders')),
            // (new ProductsPartition)->canSee(fn() => request()->user()->hasPermission('view.any.products')),

            (new RecentTicketsTable)
                ->emptyText(__("There is no registered ticket"))
                ->canSee(fn() => request()->user()->hasPermission('view.any.tickets')),

            (new RecentOrdersTable)
                ->emptyText(__("There is no registered order"))
                ->canSee(fn() => request()->user()->hasPermission('view.any.orders')),
                
            // (new LastOpenTickets)->canSee(fn() => request()->user()->hasPermission('view.any.tickets')),
        ];
    }
}
