<?php

namespace App\Product\Repeaters;

use Laravel\Nova\Fields\{ID, Text, Select};
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\Repeater\Repeatable;

class ProductVariant extends Repeatable
{
    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __("Product Variants");
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __("Product Variant");
    }
    
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Product\Entities\ProductVariant>
     */
    public static $model = \App\Product\Entities\ProductVariant::class;

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

            Text::make(__("Title"), 'definition')
                ->translatable(),

            Text::make(__("Vendor"), function($value) {
                if(isset($value['vendor_id'])) {
                    $vendor = \App\Vendor\Entities\Vendor::select(['id', 'name'])->find($value->vendor_id);
                    return $vendor?->name;
                }
                return __('Vendor');
            })->readonly(),

            Text::make(__("Price"), function($value) {
                return isset($value['face_value']) ? $value->face_value . " " . $value->face_value_currency : 0;
            })->readonly(),


            Text::make(__("Cost"), function($value) {
                return isset($value['cost']) ? $value->cost . " " . $value->cost_currency : 0;
            })->readonly(),


        ];
    }
}
