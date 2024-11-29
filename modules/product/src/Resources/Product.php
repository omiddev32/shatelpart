<?php

namespace App\Product\Resources;

use App\Fields\Tabs\Traits\{HasActionsInTabs, HasTabs};
use App\Fields\Translatable\HandlesTranslatable;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\System\NovaResource as Resource;
use Illuminate\Database\Eloquent\Model;
use App\Fields\Tabs\{Tab, Tabs};
use Illuminate\Http\Request;
// use App\Product\Tabs\Product\{GeneralTab, MediaTab, FurtherInformationTab, VariantTab, FAQTab, CountriesTab, VendorsTab};
use App\Product\Tabs\{ProductGeneralTab, ProductCharacteristicsTab, ProductZoneTab};

class Product extends Resource
{
    use HandlesTranslatable, HasTabs, HasActionsInTabs,
        ProductGeneralTab, ProductCharacteristicsTab, ProductZoneTab;

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
        return __("Products");
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
    public static $model = 'App\Product\Entities\Product';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'display_name';

    /**
     * Determine permission access this resource
     *
     * @var string
     */
    public static $permission = 'products';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = ['id', 'name', 'display_name'];

    /**
     * The relationships that should be eager loaded when performing an index query.
     *
     * @var array
     */
    public static $with = ['brand','subBrand', 'zone', 'productType','questions', 'variants', 'vendors', 'countries', 'categories', 'tags', 'productUsings', 'deliveryTypes'];

    /**
     * Register a callback to be called before the resource is deleted.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public static function beforeDelete(NovaRequest $request, Model $model)
    {
        $model->load('vendors:id,name,number_of_products_provided,number_of_products_is_not_provided');
        $model->vendors->each(function($vendor) {
            $vendor->update([
                'number_of_products_provided' => $vendor->number_of_products_provided - 1,
                'number_of_products_is_not_provided' => $vendor->number_of_products_is_not_provided + 1, 
            ]);
        });

        \DB::table('product_apis')->where('connected_to', $model->id)->update([
            'connected_to' => null
        ]);
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Tabs::make('Information', [
                Tab::make(__('General'), $this->generalTab($request)),
                Tab::make(__('Characteristics'), $this->productCharacteristicsTab($request)),
                Tab::make(__('Country and region'), $this->productZoneTab($request)),
                // Tab::make(__('Media'), $this->mediaTab($request)),
                // Tab::make(__('Further Information'), $this->furtherInformationTab($request, $beneficiaryInformationCount)),
                // Tab::make(__('Variants'), $this->variantTab($request, $priceView, $faceValues)),
                // Tab::make(__('FAQ'), $this->faqTab()),
                // Tab::make(__('Countries'), $this->countriesTab()),
                // Tab::make(__('Vendors'), $this->vendorsTab()),
            ]),
        ];
    }

    /**
     * Determine if the current user can create new resources.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public static function authorizedToCreate(Request $request)
    {
        return true;
    }

    /**
     * Determine if the user can attach any models of the given type to the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model|string  $model
     * @return bool
     */
    public function authorizedToAttachAny(NovaRequest $request, $model)
    {
        return false;
    }

    /**
     * Determine if the user can attach models of the given type to the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model|string  $model
     * @return bool
     */
    public function authorizedToAttach(NovaRequest $request, $model)
    {
        return false;
    }

    /**
     * Determine if the user can detach models of the given type to the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model|string  $model
     * @param  string  $relationship
     * @return bool
     */
    public function authorizedToDetach(NovaRequest $request, $model, $relationship)
    {
        return false;
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return array_merge($this->statusActions(), [
            (new \App\Product\Actions\ProductCategories)
                ->withName(__("Connect to category"))
                ->showOnTableRow()
        ]);
    }
}