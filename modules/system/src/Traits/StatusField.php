<?php

namespace App\System\Traits;

use Laravel\Nova\Fields\Boolean;

trait StatusField
{
	public function status()
	{
		return Boolean::make(__("Status"), 'status')
				->filterable()
                ->sortable();
	}
}