<?php

namespace App\Product\Http\Controllers\Api;

use App\Core\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Product\Entities\Product;
use App\Currency\Entities\{Currency, FormulaGroup};
use App\Product\Enums\{ProductZoneEnum, ProductTypeEnum};
use Storage;

class TorobController extends Controller
{
    /**
     * Handles Get Products List For Torob Request
     *
     * @route '/api/torob/products'
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProducts(Request $request)
    {
        $lang = app()->getLocale();

        $perPage = 100;

        if($request->perPage && $request->perPage < 200) {
            $perPage = +($request->perPage);
        }

        $pageId = '';
        $prefixUrl = env('FRONT_URL') . "/product";
        if($request->page_url && starts_with($request->page_url, $prefixUrl)) {
            $initialExplode = explode($prefixUrl, $request->page_url);
            if(isset($initialExplode[1])) {
                $explode = explode('/', $initialExplode[1]);
                if(isset($explode[2])) {
                    $pageId = $explode[2];
                }
            }
        }

        // $categoriesSearch = [];
        // if($request->categories) {
        //     $categoriesSearch = json_decode($request->categories, true);
        // }

        // $countriesSearch = [];
        // if($request->countries) {
        //     $countriesSearch = json_decode($request->countries, true);
        // }

        // $brandsSearch = [];
        // if($request->brands) {
        //     $brandsSearch = json_decode($request->brands, true);
        // }

        // $productsIds = [];
        // if($request->productsIds) {
        //     $productsIds = json_decode($request->productsIds, true);
        // }

        $sort = 'newest';
        // $sort = $request->sort && in_array($request->sort, ['newest', 'cheapest', 'expensive']) ? $request->sort : null;

        $data = Product::with(['categories:id,title'])

                ->where('status', true)

                // ->when(count($categoriesSearch) > 0 , function($query) use($categoriesSearch) {
                //     $query->whereHas('categories', function($query) use($categoriesSearch) {
                //         $query->whereIn('categories.id', $categoriesSearch);
                //     });
                // })

                // ->when(count($countriesSearch) > 0 , function($query) use($countriesSearch) {
                //     $query->whereHas('countries', function($query) use($countriesSearch) {
                //         $query->whereIn('countries.id', $countriesSearch);
                //     });
                // })
                
                // ->when(count($brandsSearch) > 0 , function($query) use($brandsSearch) {
                //     $query->whereHas('brand', function($query) use($brandsSearch) {
                //         $query->whereIn('brands.id', $brandsSearch);
                //     });
                // })
                                
                // ->when(count($productsIds) > 0 , function($query) use($productsIds) {
                //     $query->whereIn('id', $productsIds);
                // })
                                                
                ->when($request->page_unique , function($query) use($request) {
                    $query->where('id', $request->page_unique);
                })
                                                                
                ->when($pageId , function($query) use($pageId) {
                    $query->where('id', $pageId);
                })
                
                // ->when($request->name, function($query) use($request, $lang){
                //     $query->where('name', 'ilike', "%{$request->name}%")
                //         ->orWhere("display_name->{$lang}", 'ilike', "%{$request->name}%");
                // })
                

                ->when($sort, function($query) use($sort) {
                    if($sort === 'newest') {
                        $query->orderBy('created_at', 'desc');
                    } else if($sort === 'cheapest') {
                        $query->orderBy('min_cost', 'asc');
                    } else {
                        $query->orderBy('min_cost', 'desc');
                    }
                })

                ->paginate($perPage);

        $currentPage = $data->currentPage();
        $total = $data->total();
        $lastPage = $data->lastPage();
        $currencies = Currency::select('id', 'iso', 'currency_name', 'last_price')->where('last_price', '!=' , '0')->where('last_price', '!=' , '')->get();
        $currenciesNames = [];
        $currenciesValues = [];

        $vendor = \DB::table('vendors')->where('service_name', 'cysend')->first();

        foreach($currencies as $currency) {
            $currenciesValues[$currency->iso] = $currency->last_price;
            $currenciesNames[$currency->iso] = $currency->currency_name;
        }

        return json_response([
            'page' => $currentPage,
            'count' => $total,
            'max_pages' => $lastPage,
            'per_page' => $perPage,
            'products' => $data->map(function($product) use($lang, $currenciesValues, $currenciesNames, $vendor) {

                $status = true;
                $rate = 1;
                $currencyName = __("Dollar");

                if(isset($currenciesValues[$product->currency_price]) && isset($currenciesValues[$product->cost_currency])) {
                    $rate = $currenciesValues[$product->cost_currency];
                    $currencyName = $currenciesNames[$product->currency_price];

                    if($vendor && $vendor->balance < config('vendor.min_balance')) {
                        $status = false;
                    }

                } else {
                    $status = false;
                }

                $countries = [];

                if($product->zone == 'Others') {
                    $product->load('countries:id,name,image');
                    $countries = $product->countries->map(fn($country): array => [
                        'id' => $country->id,
                        'name' => $country->name,
                        'image' => Storage::disk('countries.1x1')->url($country->image),
                    ])->toArray();
                }

                $costCurrency = $product->cost_currency;
                $formulaGroup = FormulaGroup::with('formulas')->has('formulas')->whereHas('includeCurrencies', function($query) use($costCurrency) {
                    $query->where('iso', $costCurrency);
                })->first();

                $formulaItem = null;

                if($formulaGroup) {
                    $formulaItem = $formulaGroup->formulas
                        ->where('start_range', '<=', $product->min_cost)
                        ->where('end_range', '>=', $product->min_cost)
                        ->first();
                }

                $imageData = $this->getProductImageName($product->image);
                $slug = slugify($product->display_name ?: $product->name);

                return [
                    'page_unique' => $product->id,
                    'title' => $product->display_name,
                    'subtitle' => $product->name,
                    'slug' => $slug,
                    'page_url' => env('FRONT_URL') . "/product/{$slug}/{$product->id}",
                    'current_price' => str_replace(',', '', number_format($formulaItem ? ($this->calculation($formulaItem->formula, $product->min_cost, $rate, $formulaGroup->et, $formulaGroup->un, $formulaGroup->pr)) : 0)),
                    'image_link' => $this->getProductImage($product->image ?: ''),
                    'image_links' => [],
                    'category_name' => implode(', ', $product->categories->map(fn($category) => $category->title)->toArray()),
                    'availability' => $formulaItem ? 'instock' : 'outofstock',
                ];
            }),
        ], 200);
    }

    protected function calculation($formula, $price, $ex, $et = '', $un = '', $pr = '')
    {
        $calc = new \App\Currency\Services\CalculatePriceService;
        return $calc->calculation($formula, $price, $ex,  $et, $un, $pr);
    }

    /**
     * Get Product Image
     *
     * @param string $data nullable
     * @return string
     */
    protected function getProductImage($image = '')
    {
        return Storage::disk('products')->url($image ?: 'Sharjit-Gift-Card-Template.png');
    }

    /**
     * Get Product Image Name
     *
     * @param string $image
     * @return string
     */
    protected function getProductImageName($image)
    {
        if(Str::of($image)->endsWith('.png')) {
            return [
                'format' => 'png',
                'name' => Str::before($image, '.png'),
            ];
        } else if(Str::of($image)->endsWith('.jpg')) {
            return [
                'format' => 'jpg',
                'name' => Str::before($image, '.jpg'),
            ];
        } else if(Str::of($image)->endsWith('.jpeg')) {
            return [
                'format' => 'jpeg',
                'name' => Str::before($image, '.jpeg'),
            ];
        } else if(Str::of($image)->endsWith('.gif')) {
            return [
                'format' => 'gif',
                'name' => Str::before($image, '.gif'),
            ];
        } else {
            return [
                'format' => 'svg',
                'name' => Str::before($image, '.svg'),
            ];
        }
    }
}
