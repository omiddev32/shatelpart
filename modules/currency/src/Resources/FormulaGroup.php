<?php

namespace App\Currency\Resources;

use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\{ID, Text, Heading, FormData};
use App\System\NovaResource as Resource;
use App\Fields\SelectPlus\SelectPlus;
use Laravel\Nova\Fields\Repeater;

class FormulaGroup extends Resource
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
        return __("Price calculation formulas");
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __("Formula");
    }

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Currency\Entities\FormulaGroup';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * Determine permission access this resource
     *
     * @var string
     */
    public static $permission = 'priceCalculationFormulas';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = ['name'];

    /**
     * The relationships that should be eager loaded when performing an index query.
     *
     * @var array
     */
    public static $with = ['includeCurrencies', 'exceptCurrencies', 'formulas'];

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

            Text::make(__("Formula Name"), 'name')
                ->controlWrapperClass('md:w-1/5')
                ->rules('required')
                ->showOnPreview(),

            Text::make(__("Unique Name"), 'slug')
                ->controlWrapperClass('md:w-1/5')
                ->rules('required')
                ->creationRules('unique:formula_groups,slug')
                ->updateRules('unique:formula_groups,slug,{{resourceId}}')
                ->showOnPreview(),

            SelectPlus::make(__("Select the source currency included"), 'includeCurrencies', \App\Currency\Resources\Currency::class)
                ->label('currency_name')
                ->rules('required')
                ->withSelectAll()
                ->hideFromIndex()
                ->showOnPreview()
                ->usingDetailLabel(fn($models) => $models ? (\App\Currency\Entities\Currency::count() == $models->count() ? 'همه ارز ها' : implode('-', $models->pluck('currency_name')->toArray())) : ''),

            SelectPlus::make(__("Select the source currency except"), 'exceptCurrencies', \App\Currency\Resources\Currency::class)
                ->label('currency_name')
                ->dependsOn('includeCurrencies', function (SelectPlus $field, NovaRequest $request, FormData $formData) {
                    
                    if($formData->includeCurrencies != null && ! is_array($formData->includeCurrencies) && json_validate($formData->includeCurrencies)) {
                        $includeCurrencies = json_decode($formData->includeCurrencies, true);
                    } else {
                        $includeCurrencies = $formData->includeCurrencies;
                    }
                    if (
                        is_array($includeCurrencies) && ($count = count($includeCurrencies)) && $count == \App\Currency\Entities\Currency::count()) {



                        $field->readonly(false);
                    } else {

                        if(! is_array($formData->includeCurrencies) && $formData->includeCurrencies === null) {
                            //
                        } else {
                            $field->value = [];
                        }
                        
                        $field->readonly();
                    }
                })
                ->readonly()
                ->hideFromIndex()
                ->showOnPreview()
                ->usingDetailLabel(fn($models) => $models ? implode('-', $models->pluck('currency_name')->toArray()) : ''),

            Heading::make("
                <div class='w-full flex flex-wrap'>
                    ". $this->makeHelpBox("P", "قیمت خرید محصول / قیمت محصول") ."
                    ". $this->makeHelpBox("EX", "نرخ تبدیل ارز به تومان(ریال)") ."
                    ". $this->makeHelpBox("ET", __("Transfer rate fee")) ."
                    ". $this->makeHelpBox("UN", __("Unforeseen expenses")) ."
                    ". $this->makeHelpBox("PR", __("Profit rate")) ."
                </div>
            ")
            ->showOnPreview()
            ->asHtml(),

            Text::make(__("Transfer rate fee"), 'et')
                ->controlWrapperClass('md:w-1/5')
                ->showOnPreview(),

            Text::make(__("Unforeseen expenses"), 'un')
                ->controlWrapperClass('md:w-1/5')
                ->showOnPreview(),

            Text::make(__("Profit rate"), 'pr')
                ->controlWrapperClass('md:w-1/5')
                ->showOnPreview(),

            Repeater::make(__("Final formulas"), 'formulas')
                ->rules('required')
                ->sortable(false)
                ->uniqueField('id')
                ->repeatables([
                    \App\Currency\Repeaters\Formula::make(),
                ])
                ->asHasMany(\App\Currency\Resources\Formula::class),

            Text::make(__("Final formulas"), function() {

                $this->model()->load('formulas');

                $html = "<div class='w-full flex flex-wrap'>";

                foreach($this->formulas as $formula) {
                    $html .= '
                        <div class="w-full my-2">

                            <div class="w-full flex gap-x-2 flex-wrapp">

                                <div class="md:w-1/3 bg-gray-100 p-2">
                                    ' . __("Starting Price") .' : ' . $formula['start_range'] . '
                                </div>

                                <div class="md:w-1/3 bg-gray-100 p-2">
                                    ' . __("Ending Price") .' : ' . $formula['end_range'] . '
                                </div>
                                
                                <div class="md:w-1/3 bg-gray-100 p-2">
                                    ' . __("Final price calculation formula") .' : ' . $formula['formula'] . '
                                </div>
                                
                            </div>

                        </div>
                    ';
                }


                return "{$html}</div>";
            })
            ->showOnPreview()
            ->asHtml()
            ->onlyOnDetail()
        ];
    }

    public function makeHelpBox($symbol, $title)
    {
        return "
            <div class='md:w-1/5 flex flex-wrap'>
                <div class='border border-[#1C7EA5] flex h-8 items-center justify-center pt-1 rounded-lg text-[#1C7EA5] w-8'>
                    {$symbol}
                </div>
                <span class='ml-4 mt-2 text-[#1C7EA5]'>
                    {$title}
                </span>
            </div>

        ";
    }
}