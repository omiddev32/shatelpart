<?php

namespace App\Product\Resources;

use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\{ID, BelongsTo, Text, Boolean};
use App\System\NovaResource as Resource;

class ProductVariant extends Resource
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
     * @var string
     */
    public static $model = 'App\Product\Entities\ProductVariant';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'display_name';

    /**
     * Determine permission access this resource
     *
     * @var string
     */
    public static $permission = 'products';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = ['display_name'];

    /**
     * The relationships that should be eager loaded when performing an index query.
     *
     * @var array
     */
    public static $with = ['product'];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            // ID::make(__('ID'),'id')
            //     ->sortable()
            //     ->onlyOnIndex(),

            BelongsTo::make(__("Product"), 'product', Product::class),

            Text::make(__("Display Name"), 'display_name')
                ->translatable()
                ->rulesFor(config('app.locale'), [
                    'required',
                ]),

            Boolean::make(__("Selected"), 'selected'),

        ];
    }
}