<?php

namespace App\Product\Tabs\Product;

use Laravel\Nova\Fields\Heading;

trait VariantTab
{
	public function variantTab($request, $priceView, $faceValues)
	{
		return [
            Heading::make($priceView)
                ->canSee(function() use($faceValues) {
                    if($faceValues->count()) {
                        $first = $faceValues->first();
                        return $first->type != 'range';
                    }
                    return false;
                })
                ->asHtml(),
		];
	}
}