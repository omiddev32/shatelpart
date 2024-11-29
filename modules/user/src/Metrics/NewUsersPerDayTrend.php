<?php

namespace App\User\Metrics;

use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Trend;
use Laravel\Nova\Nova;
use App\User\Entities\User;
use Morilog\Jalali\Jalalian;

class NewUsersPerDayTrend extends Trend
{
    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name()
    {
        return __("New Users");
    }

    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $result = $this->countByDays($request, User::class, 'created_at');
        $trendValue = [];
        foreach($result->trend as $key => $value) {
            $trendValue[Jalalian::forge($key)->format('%B %d، %Y')] = $value / 10;
        }
        $result->trend = $trendValue;
        $result->value = number_format($result->value)  . ' نفر';
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
        return 'new-users-per-day-trend';
    }
}
