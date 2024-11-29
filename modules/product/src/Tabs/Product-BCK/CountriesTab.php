<?php

namespace App\Product\Tabs\Product;

use Laravel\Nova\Fields\BelongsToMany;

trait CountriesTab
{
	public function countriesTab()
	{
		return [
            BelongsToMany::make(__("Countries"), 'countries', \App\Country\Resources\Country::class)
                ->searchable()
                ->filterable(),
		];
	}
}