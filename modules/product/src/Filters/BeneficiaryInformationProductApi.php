<?php

namespace App\Product\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class BeneficiaryInformationProductApi extends Filter
{
    /**
     * Get the displayable name of the filter.
     *
     * @return string
     */
    public function name()
    {
        return __("Beneficiary Information");
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
        return $query->when($value == 'withInformation', function($query) {
            $query->where('beneficiary_information', '!=', '[]');
        })->when($value == 'withoutInformation', function($query) {
            $query->where('beneficiary_information', '[]');
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
            __("Contains useful information") => 'withInformation',
            __("No Beneficiary Information") => 'withoutInformation'
        ];
    }
}