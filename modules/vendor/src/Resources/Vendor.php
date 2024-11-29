<?php

namespace App\Vendor\Resources;

use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\{ID, Text, Number, BelongsToMany, HasMany};
use App\System\NovaResource as Resource;

class Vendor extends Resource
{
    /**
     * Get the logical group associated with the resource.
     *
     * @return string
     */
    public static function group()
    {
        return __("Shop");
    }

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __("Vendors");
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __("Vendor");
    }

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Vendor\Entities\Vendor';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * Determine permission access this resource
     *
     * @var string
     */
    public static $permission = 'vendors';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = ['name', 'service_name'];

    /**
     * The relationships that should be eager loaded when performing an index query.
     *
     * @var array
     */
    public static $with = ['products', 'productApis'];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make(__('ID'),'id')
                ->sortable()
                ->onlyOnIndex(),

            Text::make(__("Name"), 'name'),

            Text::make(__("Account Balance"), function() {
                $balance = number_format($this->balance);
                $currency = __($this->currency);
                return "{$balance} {$currency}";
            }),

            Number::make(__("Number of products provided"), 'number_of_products_provided')
                ->textAlign('left')
                ->sortable(),

            Number::make(__("The number of products is not provided"), 'number_of_products_is_not_provided')
                ->textAlign('left')
                ->sortable(),

            Number::make(__("Priority"), 'priority')
                ->textAlign('left')
                ->sortable(),

            $this->timestamp(__("The latest product repository update"), 'latest_product_updates'),

            $this->status()->exceptOnForms(),

            // BelongsToMany::make(__("Products provided"), 'products', \App\Product\Resources\Product::class),

            // HasMany::make(__("Products not provided"), 'productApis', \App\Product\Resources\ProductRepository::class),
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return array_merge([
            (new \App\Vendor\Actions\UpdateVendorBalanceAction)
                ->showInline(true)
                ->showOnDetail(true)
                ->withName(__('Account balance update')),
        ], $this->statusActions([1, 2]));
    }

    /**
     * Determine if the user can run the given action.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Laravel\Nova\Actions\Action  $action
     * @return bool
     */
    public function authorizedToRunAction(NovaRequest $request, \Laravel\Nova\Actions\Action $action)
    {
        return true;
    }

    /**
     * Determine if the user can run the given action.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Laravel\Nova\Actions\DestructiveAction  $action
     * @return bool
     */
    public function authorizedToRunDestructiveAction(NovaRequest $request, \Laravel\Nova\Actions\DestructiveAction $action)
    {
        return true;
    }
}