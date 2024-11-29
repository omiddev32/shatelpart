<?php

namespace App\Product\Tabs;

use Laravel\Nova\Fields\{BelongsTo, FormData};
use App\Fields\SelectPlus\SelectPlus;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Country\Entities\Country;

trait ProductZoneTab
{
	public function productZoneTab($request)
	{
		return [

            BelongsTo::make(__("Zone"), 'zone', \App\Country\Resources\Zone::class),

            SelectPlus::make(__("Countries"), 'countries', \App\Country\Resources\Country::class)
                ->ajaxSearchable(function ($search) {
                    $lang = app()->getLocale();
                    return Country::select(['id', 'name'])->where("name->{$lang}", 'ilike',  "%{$search}%")->get();
                })
                ->dependsOn('zone', function (SelectPlus $field, NovaRequest $request, FormData $formData) {
                	if(! $formData->zone) {
                		$field->readonly();
                	} else if($formData->zone == 2){
                		$field->value = [];
                		$field->maxSelections(1);
                	} else if($formData->zone == 1) {
                        $field->value = [];
                        $field->readonly();
                    } else {
                        $zone = \App\Country\Entities\Zone::with('countries:id,name')->find($formData->zone);
                        if($zone && $zone->countries->count()) {
                            $field->value = $zone->countries->map(fn($country) : array => [
                                'id' => $country->id,
                                'label' => $country->name,
                            ])->toArray();
                        } else {
                            $field->value = [];
                        }
                    }
                })
                ->label('name')
                ->rules('required')
                ->hideFromIndex()
                ->showOnPreview()
                ->usingDetailLabel(fn($models) => implode('-', $models->pluck('name')->toArray())),


		];
	}
}