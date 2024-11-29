<?php

namespace App\Currency\Resources;

use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\{ID, Text, Number, Select};
use App\System\NovaResource as Resource;

class Currency extends Resource
{
    /**
     * Get the logical group associated with the resource.
     *
     * @return string
     */
    public static function group()
    {
        return __("General Settings");
    }
    
    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __("Currencies");
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __("Currency");
    }

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Currency\Entities\Currency';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'currency_name';

    /**
     * Determine permission access this resource
     *
     * @var string
     */
    public static $permission = 'currencies';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = ['currency_name', 'iso'];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make(__('ID'),'id')
                ->sortable()
                ->onlyOnIndex(),

            Text::make(__("Display Name"), 'currency_name')
                ->translatable()
                ->showOnPreview()
                ->rulesFor(config('app.locale'), [
                    'required',
                ]),

            Text::make(__("Currency Code"), 'iso')
                ->sortable()
                ->showOnPreview()
                ->readonly(),

            Text::make(__("Currency Code (Numeric)"), 'iso')
                ->sortable()
                ->showOnPreview()
                ->readonly(),

            Number::make(__("Last Price"), 'last_price')
                ->sortable()
                ->textAlign('left')
                ->showOnPreview()
                ->exceptOnForms(),

            Select::make(__("Driver"), 'driver_last_price_update')
                ->controlWrapperClass('md:w-1/5')
                ->options([
                    'NAVASAN' => __("Navasan"),
                    'PERSIAN_API' => __("Persian Api"),
                ])
                ->rules('required'),

            $this->timestamp('تاریخ آخرین بروزرسانی', 'last_price_update'),
        ];
    }
}