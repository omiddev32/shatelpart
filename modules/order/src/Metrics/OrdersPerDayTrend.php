<?php

namespace App\Order\Metrics;

use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Trend;
use Laravel\Nova\Nova;
use App\Order\Entities\Order;
use App\Order\Enums\OrderStatusEnum;
use Morilog\Jalali\Jalalian;

class OrdersPerDayTrend extends Trend
{
    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name()
    {
        return __("Daily Sales");
    }

    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $result = $this->sumByDays($request, Order::where('status', OrderStatusEnum::SUCCESS), 'price_paid', 'paid_at');
        $trendValue = [];
        foreach($result->trend as $key => $value) {
            $trendValue[Jalalian::forge($key)->format('%B %d، %Y')] = $value / 10;
        }
        $result->trend = $trendValue;
        $result->value = number_format($result->value / 10) . ' تومان';
        return $result;
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
            7 => Nova::__('The last 7 days'),
            14 => Nova::__('14 Days'),
            30 => Nova::__('30 Days'),
            60 => Nova::__('60 Days'),
            90 => Nova::__('90 Days'),
        ];
    }

    /**
     * Determine the amount of time the results of the metric should be cached.
     *
     * @return \DateTimeInterface|\DateInterval|float|int|null
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
        return 'orders-per-day-trend';
    }
}
