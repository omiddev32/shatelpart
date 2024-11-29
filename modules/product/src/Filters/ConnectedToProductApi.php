<?php

namespace App\Product\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class ConnectedToProductApi extends Filter
{
    /**
     * Get the displayable name of the filter.
     *
     * @return string
     */
    public function name()
    {
        return __("Connected / Not Connected");
    }

    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        return $query->when($value == 'connected', function($query) {
            $query->whereNotNull('connected_to');
        })->when($value == 'notConnected', function($query) {
            $query->whereNull('connected_to');
        });
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        return [
            __("Connected") => 'connected',
            __("Not Connected") => 'notConnected'
        ];
    }
}