<?php

namespace App\Order\Metrics;

use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\MetricTableRow;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Metrics\Table;
use App\Order\Entities\Order;
use App\Order\Enums\OrderStatusEnum;

class RecentOrdersTable extends Table
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
        return "5 " . __("Recent Orders");
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

        foreach(Order::with(['product'])
            ->where('status', OrderStatusEnum::SUCCESS)
            ->latest()
            ->take(5)
            ->get() as $order) {
            $orders[] = MetricTableRow::make()
                ->icon('check-circle')
                ->iconClass('text-green-500')
                ->title("{$order->product->display_name} ( {$order->variant_value} {$order->variant->currency->currency_name} )")
                ->subtitle(number_format(($order->price_paid) / 10) . ' تومان')
                ->actions(function() use($order){
                    return [
                        MenuItem::make(
                            __("Details"),
                            "/resources/orders/{$order->id}"
                        )
                    ];
                });
        }

        return $orders;
    }
}