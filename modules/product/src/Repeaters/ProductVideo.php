<?php

namespace App\Product\Repeaters;

use Laravel\Nova\Fields\{Text, URL, Image};
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\Repeater\Repeatable;

class ProductVideo extends Repeatable
{
    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __("Product Videos");
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __("Product Video");
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
            Text::make(__("Title"), 'title')
                ->rules('required'),

            URL::make(__("Link"), 'link')
                ->rules('required'),

            Image::make(__("Cover"), 'cover')
                ->disk('products.video-covers')
                ->deletable(false)
                ->prunable()
                ->creationRules(['required', 'image', 'mimes:jpg,jpeg,png,gif'])
                ->updateRules(function (NovaRequest $request) {
                    $model = $request->findModelOrFail();

                    return $model->cover ? ['nullable'] : ['required', 'image', 'mimes:jpg,jpeg,png,gif'];
                }),
        ];
    }
}
