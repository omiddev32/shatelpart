<?php

namespace App\Product\Tabs\Product;

use Laravel\Nova\Fields\{ID, BelongsTo, Text, Select, Number};
use App\Product\Enums\{ProductZoneEnum, ProductTypeEnum};

trait GeneralTab
{
	public function generalTab($request)
	{
		return [

            ID::make(__('ID'),'id')
                ->sortable()
                ->onlyOnIndex(),

            BelongsTo::make(__("Brand"), 'brand', \App\Brand\Resources\Brand::class)
                ->searchable()
                ->showOnPreview()
                ->filterable()
                ->sortable(),

            Text::make(__("Name"), 'name')
                ->rules('required')
                ->showOnPreview()
                ->help(__("English Name Only"))
                ->sortable(),

            Text::make(__("Display Name"), 'display_name')
                ->translatable()
                ->showOnPreview()
                ->rulesFor(config('app.locale'), [
                    'required',
                ]),

            Select::make(__("Type"), 'type')
                ->rules('required')
                ->readonly()
                ->showOnPreview()
                ->options(ProductTypeEnum::map())
                ->displayUsingLabels()
                ->hideFromIndex()
                ->filterable(),

            Select::make(__("Zone"), 'zone')
                ->showOnPreview()
                ->options(ProductZoneEnum::map())
                ->readonly()
                ->hideFromIndex()
                ->displayUsingLabels()
                ->filterable(),

            Select::make(__("Price Type"), 'price_type')
                ->showOnPreview()
                ->options([
                    'fixed' => __("Specific prices"),
                    'range' => __("Price range"),
                    'single' => __("Unit price"),
                ])
                ->readonly()
                ->hideFromIndex()
                ->displayUsingLabels()
                ->filterable(),

            Number::make(__("From"), 'min_price')
                ->textAlign('left')
                ->exceptOnForms()
                ->sortable(),

            Number::make(__("To"), 'max_price')
                ->textAlign('left')
                ->exceptOnForms()
                ->sortable(),

            Text::make(__("Currency"), 'currency_price')
                ->exceptOnForms()
                ->filterable()
                ->sortable(),

            $this->status()->exceptOnForms(),

		];
	}
}
