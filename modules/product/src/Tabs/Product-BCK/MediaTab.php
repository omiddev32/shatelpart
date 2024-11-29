<?php

namespace App\Product\Tabs\Product;

use App\Fields\ImageWithThumbs\ImageWithThumbs;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Product\Repeaters\ProductVideo;
use Laravel\Nova\Fields\{Boolean};
use Laravel\Nova\Fields\Repeater;
use Illuminate\Http\Request;

trait MediaTab
{
	public function mediaTab($request)
	{
		return [
            ImageWithThumbs::make(__("Image"), 'image')
                ->storeAs(function(Request $request) {
                    $file = $request->file('image');
                    $name = $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();
                    return "{$name}.{$extension}";
                })
                ->disk('products')
                ->deletable(false)
                ->showOnPreview()
                ->prunable()
                ->creationRules(['nullable', 'image', 'mimes:jpg,jpeg,png,gif,svg'])
                ->updateRules(function (NovaRequest $request) {
                    $model = $request->findModelOrFail();
                    return $model->image ? ['nullable'] : ['image', 'mimes:jpg,jpeg,png,gif,svg'];
                }),

            Boolean::make(__("Videos are active"), 'videos_status')
                ->hideFromIndex()
                ->default(true),

            Repeater::make(__("Videos"), 'videos')
                ->repeatables([
                    ProductVideo::make(),
                ])
                ->maxRow(5)
                ->asJson()
		];
	}
}