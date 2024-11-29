<?php

namespace App\Order\Settings;

use App\Packages\Settings\Settings;
use Laravel\Nova\Fields\{Text, Number, Boolean, Heading, Select};
use Laravel\Nova\Menu\MenuItem;
use Illuminate\Http\Request;

class OrdersSettings extends Settings
{
    /**
     * Get the displayable singular label of the tool.
     *
     * @return string
     */
    public function label()
    {
        return __("Orders Settings");
    }

    /**
     * The model the settings corresponds to.
     *
     * @var string
     */
    public $model = \App\Order\Entities\OrderSetting::class;

    /**
     * Determine permission access this resource
     *
     * @var string
     */
    public $permission = 'ordersSettings';

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
    	return [

            Heading::make("
                <div class='w-full font-bold text-[#1C7EA5]'>
                    ". __("VAT") ." 
                </div>  
            ")->asHtml(),

            Number::make(__("Value added tax rate - VAT"), 'vat_rate')
                ->controlWrapperClass('md:w-1/5')
                ->min(1)
                ->max(100)
                ->help('%')
                ->rules('required'),

            Boolean::make(__("VAT is active"), 'vat_status'),

            Heading::make("
                <div class='w-full font-bold text-[#1C7EA5]'>
                    ". __("The duration of calling currency rates / validity period of orders") ." 
                </div>  
            ")->asHtml(),

            Number::make(__("The duration of calling currency rates"), 'currency_api_duration')
                ->controlWrapperClass('md:w-1/5')
                ->help(__("Minutes - maximum :minutes minutes", [
                    'minutes' => 60
                ]))
                ->max(1)
                ->max(60)
                ->rules('required'),

            Number::make(__("Validity period of orders"), 'order_validity_period')
                ->controlWrapperClass('md:w-1/5')
                ->help(__("Minutes - maximum :minutes minutes", [
                    'minutes' => 30
                ]))
                ->max(1)
                ->max(30)
                ->rules('required'),

            Heading::make("
                <div class='w-full font-bold text-[#1C7EA5]'>
                    ". __("Currency exchange rates api driver") ." 
                </div>  
            ")->asHtml(),

            Select::make(__("Driver"), 'currency_api_driver')
                ->controlWrapperClass('md:w-1/5')
                ->options([
                    'NAVASAN' => __("Navasan"),
                    'PERSIAN_API' => __("Persian Api"),
                ])
                ->rules('required'),

        ];
    }

    /**
     * Build the menu that renders the navigation links for the tool.
     *
     * @param  \Illuminate\Http\Request $request
     * @return mixed
     */
    public function menu(Request $request)
    {
        if($this->seeCallback !== false) {
            return MenuItem::make($this->label())
                ->path('/settings/'. $this->uriKey())
                ->mainGroup(__("Orders"));
        }
        return ;
    }
}
