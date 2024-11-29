<?php

namespace App\Product\Tabs\Product;

use Laravel\Nova\Fields\BelongsToMany;

trait VendorsTab
{
	public function vendorsTab()
	{
		return [
            BelongsToMany::make(__("Vendors"), 'vendors', \App\Vendor\Resources\Vendor::class)
                ->filterable(),	
		];
	}
}