<?php

namespace App\Product\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\{ActionFields, Select, Text, Heading, BelongsTo, Hidden};
use App\Product\Entities\ProductVariant;
use App\Fields\AjaxSelect\AjaxSelect;
use Illuminate\Database\Eloquent\Builder;

class ConnectToProduct extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * resoruce data
     *
     * @var model
     */
    private $data;

    /**
     * The size of the modal. Can be "sm", "md", "lg", "xl", "2xl", "3xl", "4xl", "5xl", "6xl", "7xl".
     *
     * @var string
     */
    public $modalSize = '3xl';

    /**
     * Set recource data
     *
     * @param $data
     * @return $this
     */
    public function data($data) 
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields $fields
     * @param  \Illuminate\Support\Collection $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $isCreateMode = true;

        $systemLanguages = config('translatable.locales');
        $productRepository = $models->first();
        $productRepository->load('vendor:id,name');
        $productRepository->load('faceValues');
        $faceValuesCount = $productRepository->faceValues->count();
        $firstFaceValue = $productRepository->faceValues->first();

        if($fields->connectedTo) {
            $isCreateMode = false;
            $product = \App\Product\Entities\Product::find($fields->connectedTo);
        } else {
            $product = new \App\Product\Entities\Product;
            $product->name = $fields->name;
            /* Display name */

            $defaultLangForProduct = $productRepository->vendor->id == 3 ? 'fa' : 'en';

            $displayName = [$defaultLangForProduct => $fields->name];
            foreach(array_diff(array_keys($systemLanguages), [$defaultLangForProduct]) as $language) {
                $displayName[$language] = null;
            }
            $product->display_name = $displayName;

            $product->zone = $productRepository->zone;
            $product->type = $productRepository->type;

            /* Introduction */
            $productDescription = json_decode($productRepository->description, true);
            $description = [];
            if(count($productDescription)) {
                $existsDescriptionForSystem = [];
                foreach($productDescription as $item) {
                    if(isset($systemLanguages[$item['language']])) {
                        $existsDescriptionForSystem[] = $item['language'];
                        $description[$item['language']] = $item['text'];
                    }
                }
                foreach(array_diff(array_keys($systemLanguages), $existsDescriptionForSystem) as $language) {
                    $description[$language] = null;
                }
            }
            $product->introduction = $description;

            /* Beneficiary Information */

            $beneficiaryInformation = json_decode($productRepository->beneficiary_information, true);
            $beneficiaryList = [];
            if(count($beneficiaryInformation)) {
                foreach($beneficiaryInformation as $item) {
                    $name[$defaultLangForProduct] = $item['name'];
                    $beneficiaryDescription[$defaultLangForProduct] = $item['description'] ?: null;
                    foreach(array_diff(array_keys($systemLanguages), [$defaultLangForProduct]) as $language) {
                        $name[$language] = $item['name'];
                        $beneficiaryDescription[$language] = $item['description'] ?: null;
                    }

                    $beneficiaryList[] = [
                        'type' => 'product-beneficiary',
                        'fields' => [
                            'name' => $item['name'],
                            'display_name' => $name,
                            'description' => $beneficiaryDescription,
                            'type' => $item['type'],
                            'pattern' => $item['pattern'],
                            'required' => $item['required'],
                        ]
                    ];
                }
            }
            $product->beneficiary_information = $beneficiaryList;

            $product->application = [];
            $product->usage_method = [];
            $product->videos = [];

            /* Status */
            $product->promotion = $productRepository->promotion;
            $product->maintenance = $productRepository->maintenance;
            if($faceValuesCount == 1 && $firstFaceValue->type === 'fixed') {
                $product->price_type = 'single';
                $product->min_price = $firstFaceValue->face_value;
                $product->max_price = $firstFaceValue->face_value;

                $product->cost_currency = $firstFaceValue->cost_currency;
                $product->min_cost = $firstFaceValue->cost;
                $product->max_cost = $firstFaceValue->cost;

            } else if($faceValuesCount == 1 && $firstFaceValue->type === 'range') {
                $product->price_type = 'range';
                $product->min_price = $firstFaceValue->face_value;
                $product->max_price = $firstFaceValue->max_face_value;

                $product->cost_currency = $firstFaceValue->cost_currency;
                $product->min_cost = $firstFaceValue->cost;
                $product->max_cost = $firstFaceValue->max_cost;

            } else if($faceValuesCount > 1 && $firstFaceValue->type === 'fixed') {
                $product->price_type = 'fixed';
                $product->min_price = $firstFaceValue->face_value;
                $product->max_price = $productRepository->faceValues->last()->face_value;

                $product->cost_currency = $firstFaceValue->cost_currency;
                $product->min_cost = $firstFaceValue->cost;
                $product->max_cost = $productRepository->faceValues->last()->cost;
            }
            $product->currency_price = $firstFaceValue->face_value_currency;
            $product->save();
        }

        $vendor = [];

        \DB::table('product_variants')->insert($productRepository->faceValues->map(function($faceValue) use($product, & $vendor){
            $definition = '';
            if($faceValue->type == 'fixed') {
                if($faceValue->definition) {
                    $definition = $faceValue->definition;
                } else {
                    $definition =  ($faceValue->face_value . ' ' . $faceValue->face_value_currency);
                }
            }
            $vendor = $faceValue->vendor_id;
            return [
                'vendor_id' => $faceValue->vendor_id,
                'product_id' => $product->id,
                'face_value_id' => $faceValue->face_value_id,
                'type' => $faceValue->type,
                'face_value_currency' => $faceValue->face_value_currency,
                'face_value' => $faceValue->face_value,
                'max_face_value' => $faceValue->max_face_value,
                'face_value_step' => $faceValue->face_value_step,
                'definition' => $definition,
                'cost_currency' => $faceValue->cost_currency,
                'cost' => $faceValue->cost,
                'max_cost' => $faceValue->max_cost,
                'promotion' => $faceValue->promotion,
            ];
        })->toArray());

        if($isCreateMode) {
            $product->countries()->attach(\DB::table('countries')->whereIn('original_id', json_decode($productRepository->countries, true))->pluck('id')->toArray());
        }

        $vendors = [];
        $vendorDB = \App\Vendor\Entities\Vendor::select(['id', 'number_of_products_provided', 'number_of_products_is_not_provided'])->find($vendor);
        $vendorDB->update([
            'number_of_products_provided' => $vendorDB->number_of_products_provided + 1,
            'number_of_products_is_not_provided' => $vendorDB->number_of_products_is_not_provided - 1,
        ]);
        if($vendor == 2) {
            $vendors[$vendor] = ['has_priority' => true];
        } else {
            $vendors[$vendor] = $vendor;
        }
        $product->vendors()->attach($vendors);
        $productRepository->connected_to = $product->id;
        $productRepository->update();

        return Action::message($isCreateMode ? __("The product was created successfully.") : __("The product connected successfully."));
    }

    /**
     * Get the fields available on the action.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        $this->data->load('vendor');
        // $similarProducts = [];
        // foreach(
        //     \App\Product\Entities\Product::select(['id', 'name'])
        //         ->where('name', 'LIKE', "%{$this->data?->name}%")
        //         ->whereHas('vendors', function($query) {
        //             $query->where('vendor_id', '!=', $this->data?->vendor?->id);
        //         })->get()
        //         as 
        //     $product
        // ) {
        //     $similarProducts[$product->id] = $product->name;
        // }

        return [

            Text::make(__('Name'), 'name')
                ->default($this->data?->name)
                ->rules('required'),

            Text::make(__('Vendor'), 'vendor')
                ->default($this->data?->vendor?->name)
                ->readonly()
                ->rules('required'),

            AjaxSelect::make(__("Similar products available"), 'connectedTo')
                ->setUrl('/panel-api/products/provided')
                ->setValueKey('id')
                ->setLabelKey('name')
                ->responsive(),

            // BelongsTo::make(__("Similar products available"), 'connectedTo', \App\Product\Resources\Product::class)
            //     ->searchable()
            //     // ->relatableQueryUsing(function (NovaRequest $request, Builder $query) {
            //     //     $query->whereDoesntHave('vendors', function($query) {
            //     //         $query->where('vendors.id', '!=',  $this->data?->vendor?->id);
            //     //     });
            //     // })
            //     ->canSee(fn() => $this->data?->vendor?->id !== 2)
            //     ->nullable()

            // Select::make(__("Similar products available"), 'product_id')
            //     // ->searchable()
            //     ->nullable()
            //     ->options($similarProducts)
            //     ->canSee(fn() => count($similarProducts) > 0),

        ];
    }
}