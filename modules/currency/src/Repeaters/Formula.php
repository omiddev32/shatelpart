<?php

namespace App\Currency\Repeaters;

use Laravel\Nova\Fields\{ID, Number, Text};
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\Repeater\Repeatable;

class Formula extends Repeatable
{
    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __("Final formulas");
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __("Final formula");
    }
    
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Currency\Entities\Formula>
     */
    public static $model = \App\Currency\Entities\Formula::class;

    /**
     * Get the fields displayed by the repeatable.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::hidden(),

            Number::make(__("Starting Price"), 'start_range')
                ->rules('required')
                ->step('any'),

            Number::make(__("Ending Price"), 'end_range')
                ->rules('required')
                ->step('any'),

            Text::make(__("Final price calculation formula"), 'formula')
                ->rules('required'),
        ];
    }
}
