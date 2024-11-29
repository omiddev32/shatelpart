<?php

namespace App\Product\Tabs;

use App\Fields\SelectPlus\SelectPlus;
use Laravel\Nova\Fields\{Text, Boolean, Select, BelongsTo, FormData};
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Brand\Entities\Brand;

trait ProductCharacteristicsTab
{
	public function productCharacteristicsTab($request)
	{
		return [
            
            SelectPlus::make(__("Categories"), 'categories', \App\Category\Resources\Category::class)
                ->ajaxSearchable(function ($search) {
                    $lang = app()->getLocale();
                    return \App\Category\Entities\Category::select(['id', 'title'])->where("title->{$lang}", 'ilike',  "%{$search}%")->get();
                })
                ->label('title')
                ->hideFromIndex()
                ->showOnPreview()
                ->usingDetailLabel(fn($models) => implode('-', $models->pluck('title')->toArray())),

            Boolean::make(__("Display categories as tags"), 'meta_data.categories_tagable')
                ->hideFromIndex()
                ->default(true),

            Select::make(__("Brand"), 'brand_id')
                ->rules('required')
                ->showOnPreview()
                ->options(function() {
                	$list = [];
                	foreach(Brand::query()->select('id', 'name', 'brand_id')->whereNull('brand_id')->get() as $brand) {
                		$list[$brand->id] = $brand->name;
                	}
                	return $list;
                })
                ->displayUsingLabels()
                ->filterable(),

            Select::make(__("Sub Brand"), 'sub_brand_id')
                ->rules('nullable')
                ->showOnPreview()
                ->dependsOn('brand_id', function (Select $field, NovaRequest $request, FormData $formData) {
                    if(! $formData->brand_id) {
                        $field->value = null;
                        $field->options([]);
                        $field->readonly();
                    } else {
                        $list = [];
                        foreach(Brand::query()->select('id', 'name', 'brand_id')->where('brand_id', $formData->brand_id)->get() as $brand) {
                            $list[$brand->id] = $brand->name;
                        }
                        $field->options($list);
                        $field->readonly(false);
                    }

                })
                ->displayUsingLabels()
                ->filterable(),

            BelongsTo::make(__("Product Type"), 'productType', \App\Product\Resources\ProductType::class),

            SelectPlus::make(__("How to use"), 'productUsings', \App\Product\Resources\ProductUsing::class)
                ->ajaxSearchable(function ($search) {
                    $lang = app()->getLocale();
                    return \App\Product\Entities\Using::select(['id', 'title'])->where("title->{$lang}", 'ilike',  "%{$search}%")->get();
                })
                ->label('title')
                ->rules('required')
                ->hideFromIndex()
                ->showOnPreview()
                ->usingDetailLabel(fn($models) => implode('-', $models->pluck('title')->toArray())),

            SelectPlus::make(__("Delivery method"), 'deliveryTypes', \App\Product\Resources\DeliveryType::class)
                ->ajaxSearchable(function ($search) {
                    $lang = app()->getLocale();
                    return \App\Product\Entities\DeliveryType::select(['id', 'title'])->where("title->{$lang}", 'ilike',  "%{$search}%")->get();
                })
                ->label('title')
                ->rules('required')
                ->hideFromIndex()
                ->showOnPreview()
                ->usingDetailLabel(fn($models) => implode('-', $models->pluck('title')->toArray())),
		];
	}
}