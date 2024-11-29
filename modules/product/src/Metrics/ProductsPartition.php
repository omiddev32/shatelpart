<?php

namespace App\Product\Metrics;

use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;
use App\Product\Entities\Product;
use App\Product\Enums\ProductTypeEnum;

class ProductsPartition extends Partition
{
    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name()
    {
        return __("Products");
    }

    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $products = [];

        foreach(ProductTypeEnum::map() as $status) :
            $products[$status] = 0;
        endforeach;

        foreach(Product::select('id', 'display_name', 'type')->get() as $product) :
            $products[ProductTypeEnum::instanceFromKey($product->type)->value()] += 1;
        endforeach;

        return $this->result($products);
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return  \DateTimeInterface|\DateInterval|float|int
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
        return 'products-partition';
    }
}