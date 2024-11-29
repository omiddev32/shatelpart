<?php

namespace App\Order\Metrics;

use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;
use App\Order\Entities\Order;
use App\Order\Enums\OrderStatusEnum;

class OrdersPartition extends Partition
{
    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name()
    {
        return __("Orders");
    }

    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $orders = [];

        foreach(OrderStatusEnum::map() as $status) :
            $orders[$status] = 0;
        endforeach;

        foreach(Order::select('id', 'status')->get() as $order) :
            $orders[OrderStatusEnum::instanceFromKey($order->status)->value()] += 1;
        endforeach;

        return $this->result($orders);
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
        return 'orders-partition';
    }
}