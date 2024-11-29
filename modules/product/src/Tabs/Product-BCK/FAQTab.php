<?php

namespace App\Product\Tabs\Product;

use Laravel\Nova\Fields\BelongsToMany;

trait FAQTab
{
	public function faqTab()
	{
		return [
            BelongsToMany::make(__("FAQ"), 'questions', \App\Question\Resources\Question::class)
                ->searchable(),
		];
	}
}