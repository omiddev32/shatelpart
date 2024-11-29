<?php

namespace App\Product\Repeaters;

use Laravel\Nova\Fields\{Text, Textarea};
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\Repeater\Repeatable;

class ProductBeneficiary extends Repeatable
{
    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __("Product Beneficiaries");
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __("Product Beneficiary");
    }

    /**
     * Get the fields displayed by the repeatable.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [

            Text::make(__("Field Name"), 'name')
                ->readonly(),

            Text::make(__("Display Name"), 'display_name')
                ->translatable(),            

            Textarea::make(__("Description"), 'description')
                ->translatable(),

            Text::make(__("Type"), 'type')
                ->readonly(),

            Text::make(__("Pattern"), 'pattern')
                ->readonly(),
                
            Text::make(__("Required"), 'required')
                ->readonly(),
        ];
    }
}
