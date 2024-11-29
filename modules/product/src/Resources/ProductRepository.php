<?php

namespace App\Product\Resources;

use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\{ID, Text, BelongsTo, Select, Boolean, Number};
use Laravel\Nova\Fields\KeyValue;
use Illuminate\Support\HtmlString;
use App\System\NovaResource as Resource;

class ProductRepository extends Resource
{
    /**
     * Get the logical group associated with the resource.
     *
     * @return string
     */
    public static function group()
    {
        return __("Shop");
    }

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __("Products Repository");
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __("Product");
    }

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Product\Entities\ProductApi';

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
    public static $permission = 'productsRepository';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = ['product_id', 'name', 'connectedTo.name'];

    /**
     * The relationships that should be eager loaded when performing an index query.
     *
     * @var array
     */
    public static $with = ['vendor', 'connectedTo'];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        

        return [
            // ID::make(__('ID'),'id')
            //     ->sortable()
            //     ->hideFromIndex(),

            Text::make(__("Name"), 'name')
                ->sortable(),

            BelongsTo::make(__("Vendor"), 'vendor', \App\Vendor\Resources\Vendor::class)
                ->showOnPreview()
                ->filterable(),

            Number::make(__(__("Original product ID")), 'product_id')
                ->showOnPreview()
                ->textAlign('left')
                ->sortable(),

            Select::make(__("Type"), 'type')
                ->showOnPreview()
                ->options([
                    'instant' => __("Instant"),
                    'prepaid_code' => __("Prepaid Code"),
                    'sms' => __("SMS"),
                    'hlr' => __("HLR"),
                ])
                ->displayUsingLabels()
                ->filterable(),

            Text::make(__("Sales Price"), function() {
                $faceValues = $this->model()->load('faceValues')->faceValues;

                if($faceValues->count()) {                    
                    $first = $faceValues->first();
                    if($first->type == 'fixed') {
                        if($faceValues->count() > 1) {
                            $last = $faceValues->last();
                            return __("From") . " {$first->face_value} " . __("To") . " {$last->face_value} {$last->face_value_currency}";
                        }
                        return "{$first->face_value} {$first->face_value_currency}";
                    } 
                    if($faceValues->count() > 1) {
                        $last = $faceValues->last();
                        return __("From") . " {$first->face_value} " . __("To") . " {$last->max_face_value} {$last->face_value_currency}";
                    }
                    return __("From") . " {$first->face_value} " . __("To") . " {$first->max_face_value} {$first->face_value_currency}";
                }

                return __("Price information not found!");
            })
            ->showOnPreview(),

            Select::make(__("Zone"), 'zone')
                ->showOnPreview()
                ->options([
                    'Global' => __("Global"),
                    'Others' => __("Different"),
                    'Eurozone' => __("Eurozone"),
                ])
                ->displayUsingLabels()
                ->filterable(),

            Text::make(__("Country"), function() {
                $value = $this->getCountries(true);
                return $value ? $this->getCountries(true)?->name : "--";
            })
            ->onlyOnIndex(),

            Text::make(__("Countries"), function() {
                return implode(',', $this->getCountries()->select(['id', 'name'])->get()->pluck('name')->toArray());
            })
            ->canSee(fn() => $this->zone != 'Global')
            ->onlyOnDetail()
            ->showOnPreview(),

            BelongsTo::make(__("Connected To"), 'connectedTo', Product::class)
                ->showOnPreview(),

            Boolean::make(__("Promotion"), 'promotion')
                ->filterable()
                ->sortable(),

            Boolean::make(__("Maintenance"), 'maintenance')
                ->filterable()
                ->sortable(),

            $this->updatedAt()
                ->hideFromIndex(),

            Text::make(__("Image"), function() use($request) {
                $uri = $request->route()->uri;
                $class = ($uri == "nova-api/{resource}/{resourceId}" || $request->route()->uri === 'nova-api/{resource}/{resourceId}/preview') ? 'w-48 h-48 bject-contain' : 'h-14 w-14';
                return '<image class=" ' . $class . ' " src=" ' . $this->logo_url . ' " />';
            })
            ->asHtml()
            ->onlyOnDetail()
            ->showOnPreview(),

            Text::make(__("Beneficiary Information"), function() {
                $beneficiaryInformation = json_decode($this->beneficiary_information, true);

                $html = "";

                $count = count($beneficiaryInformation);

                foreach($beneficiaryInformation as $key => $information) {
                    $html .= $this->makeBeneficiaryInformationAsHtml($information, $count == ($key + 1));
                }

                return $html != "" ? $html : "
                    <div class='w-full'>
                        " . __("No Beneficiary Information") . "
                    </div>
                ";
            })
            ->asHtml()
            ->onlyOnDetail()
            ->showOnPreview(),

            Text::make(__("Description"), function() {
                $description = json_decode($this->description, true);
                $systemLanguages = config('translatable.locales');
                $html = "<div class='w-full'>";
                $existsDescriptionForSystem = [];
                if(count($description)) {
                    foreach($description as $item) {
                        if(isset($systemLanguages[$item['language']])) {
                            $existsDescriptionForSystem[] = $item['language'];
                            $html .= $this->makeDescriptionAsHtml($systemLanguages[$item['language']], $item['text'], $item['language']);
                        }
                    }
                    if($html != "<div class='w-full'>") {
                        foreach(array_diff(array_keys($systemLanguages), $existsDescriptionForSystem) as $language) {
                            $html .= $this->makeDescriptionAsHtml($systemLanguages[$language], __("No Explanation"), $language);
                        }
                        return "{$html}</div>";
                    }
                }
                return "
                    <div class='w-full'>
                        " . __("No Explanation") . "
                    </div>
                ";

            })
            ->asHtml()
            ->onlyOnDetail()
            ->showOnPreview(),

            Text::make(__("Prices"), function() {
                $faceValues = $this->model()->faceValues;
                $html = "";
                foreach($faceValues as $faceValue) {
                    $html .= $this->makePricesAsHtml($faceValue);
                }
                return $html;
            })
            ->canSee(function() {
                $faceValues = $this->model()->load('faceValues')->faceValues;
                if($faceValues->count()) {
                    $first = $faceValues->first();
                    return $first->type != 'range';
                }
                return false;
            })        
            ->asHtml()
            ->onlyOnDetail(),


        ];
    }

    /**
     * Make description
     *
     * @param $language
     * @param $text
     * @return string
     */
    private function makeDescriptionAsHtml(string $language, string $text, string $key)
    {
        return '
            <div class="w-full border border-blue-100 shadow rounded-lg bg-blue-100 mb-3 p-3">
                <div class="w-full">
                    <span class="text-blue-500 font-bold">
                        ' . __("Language") . ' : 
                    </span>
                    ' . $language . '
                </div>
    
                <div class="w-full my-3">
                    <span class="text-blue-500 font-bold">
                        ' . __("Text") . '
                    </span>
                    <div class="w-full mt-2" style="text-align: ' . ($key == "fa" || $key == "ar" ? "right" : "left") . '">
                        ' . $text . '
                    </div>
                </div>
            </div>
        ';
    }

    /**
     * Make Beneficiary Information
     *
     * array $information
     * @return string
     */
    private function makeBeneficiaryInformationAsHtml(array $information, bool $isLast = false)
    {
        $description = $information['description'] ?: __("No Explanation");
        $required = $information['required'] ? __("Yes") : __("No");
        $customClass = $isLast ? 'mt-4 pb-4' : 'border-b my-2 pb-4';

        return '
            <div class="w-full ' . $customClass .  '">

                <div class="w-full flex gap-x-2 flex-wrapp">
                    <div class="md:w-1/4 bg-blue-100 p-2">
                        ' . __("Field Name") . ' 
                    </div>

                    <div class="md:w-3/4 bg-blue-100 p-2">
                        ' . $information['name'] . '
                    </div>
                </div>

                <div class="w-full mt-2 flex gap-x-2 flex-wrapp">
                    <div class="md:w-1/4 bg-blue-100 p-2">
                        ' . __("Description") . ' 
                    </div>

                    <div class="md:w-3/4 bg-blue-100 p-2">
                       ' . $description . '
                    </div>
                </div>

                <div class="w-full mt-2 flex gap-x-2 flex-wrapp">
                    <div class="md:w-1/4 bg-blue-100 p-2">
                        ' . __("Type") . ' 
                    </div>

                    <div class="md:w-3/4 bg-blue-100 p-2">
                        ' . $information['type'] . '
                    </div>
                </div>
                
                <div class="w-full mt-2 flex gap-x-2 flex-wrapp">
                    <div class="md:w-1/4 bg-blue-100 p-2">
                        ' . __("Pattern") . ' 
                    </div>

                    <div class="md:w-3/4 bg-blue-100 p-2">
                       ' . $information['pattern'] . '
                    </div>
                </div>
                                
                <div class="w-full mt-2 flex gap-x-2 flex-wrapp">
                    <div class="md:w-1/4 bg-blue-100 p-2">
                        ' . __("Required") . ' 
                    </div>

                    <div class="md:w-3/4 bg-blue-100 p-2">
                        ' . $required . '
                    </div>
                </div>
                
            </div>
        ';
    }

    /**
     * Make Beneficiary Information
     *
     * array $faceValue
     * @return string
     */
    private function makePricesAsHtml(object $faceValue)
    {
        if($faceValue->type == 'fixed') {
            if($faceValue->definition) {
                return '
                    <div class="w-full my-2 pb-4">

                        <div class="w-full flex gap-x-2 flex-wrapp">
                            <div class="md:w-1/12 bg-yellow-100 p-2">
                                ' . __("Model") . ' 
                            </div>

                            <div class="md:w-7/12 bg-yellow-100 p-2">
                                ' . $faceValue['definition'] . '
                            </div>

                            <div class="md:w-1/12 bg-yellow-100 text-center p-2">
                                ' . $faceValue->face_value . ' ' . $faceValue->face_value_currency . '  
                            </div>

                            <div class="md:w-3/12 bg-yellow-100 p-2 justify-between  flex">
                                <span>' . __("Cost") .  '</span>
                                <span class="mr-3">' . number_format($faceValue->cost) . ' ' . $faceValue->cost_currency .  '</span>
                            </div>

                        </div>

                        
                    </div>
                ';
            }
            return '
                <div class="w-full my-2 pb-4">

                    <div class="w-full flex gap-x-2 flex-wrapp">
                        <div class="md:w-1/12 bg-yellow-100 p-2">
                            ' . __("Model") . ' 
                        </div>

                        <div class="md:w-8/12 bg-yellow-100 p-2">
                            ' . $faceValue->face_value . ' ' . $faceValue->face_value_currency . '  
                        </div>

                        <div class="md:w-3/12 bg-yellow-100 p-2 justify-between  flex">
                            <span>' . __("Cost") .  '</span>
                            <span class="mr-3">' . number_format($faceValue->cost) . ' ' . $faceValue->cost_currency .  '</span>
                        </div>

                    </div>

                    
                </div>
            ';
        }
    }

    /**
     * Determine if the user can run the given action.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Laravel\Nova\Actions\Action  $action
     * @return bool
     */
    public function authorizedToRunAction(NovaRequest $request, \Laravel\Nova\Actions\Action $action)
    {
        return true;
    }

    /**
     * Determine if the user can run the given action.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Laravel\Nova\Actions\DestructiveAction  $action
     * @return bool
     */
    public function authorizedToRunDestructiveAction(NovaRequest $request, \Laravel\Nova\Actions\DestructiveAction $action)
    {
        return true;
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [
            (new \App\Product\Actions\ConnectToProduct)
                ->withName(__("Connect / Create product"))
                ->confirmButtonText(__("Connect / Create"))
                ->canSee(fn() => auth()->user()->hasPermission('create.products') && $this->connected_to == null)
                ->canRun(fn() => auth()->user()->hasPermission('create.products') && $this->connected_to == null)
                ->data($this->model())
                ->onlyOnTableRow()
                ->showOnDetail()
        ];
    }

    /**
     * Get the filters available on the entity.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [
            (new \App\Product\Filters\BeneficiaryInformationProductApi),
            (new \App\Product\Filters\ConnectedToProductApi),
        ];
    }
}