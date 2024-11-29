<?php

namespace App\Product\Http\Controllers\Api;

use App\Core\Controller;
use Illuminate\Http\Request;
use App\Product\Entities\Product;
use App\Currency\Entities\{Currency, FormulaGroup};
use App\Product\Enums\{ProductZoneEnum, ProductTypeEnum};
use Illuminate\Support\Facades\Validator;
use Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{

    /**
     * Product search with preview result
     *
     * @route '/api/products/search'
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function productSearch(Request $request)
    {
        $user = auth()->guard('api')->user();
        $lang = app()->getLocale();

        $validator = Validator::make($request->all(), [
            'name' => "required"
        ]);

        if($validator->fails()) :
            return response()->json([
                'errors'=> $validator->errors()
            ] , 422);
        endif;

        // Advance Serach
        $keywords = explode(' ', $request->name);
        $keywords = array_map('trim', $keywords);

        return json_response([
            'products' => Product::select('id', 'status', 'name', 'display_name', 'image')
                ->where('status', true)
                ->where(function($query) use($keywords, $lang) {
                    $query
                        ->where(function($query) use($keywords){
                            foreach($keywords as $keyword):
                                $query->where('name', 'ilike', "%{$keyword}%");
                            endforeach;
                        })
                        ->orWhere(function($query) use($keywords, $lang){
                            foreach($keywords as $keyword):
                                $query
                                    ->where("display_name->{$lang}", 'ilike', "%{$keyword}%");
                            endforeach;
                        });
                })->get()->map(function($product) {

                    $imageData = $this->getProductImageName($product->image);

                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => slugify($product->display_name ?: $product->name),
                        'display_name' => $product->display_name,
                        'image' => $this->getProductImage($product->image ?: ''),
                        'image_thumbnails' => $product->image ? [
                            '360' => $imageData['name'] . '-360.' . $imageData['format'],
                            '640' => $imageData['name'] . '-640.' . $imageData['format'],
                            '1024' => $imageData['name'] . '-1024.' . $imageData['format'],
                        ] : [],

                    ];
                })->toArray()
        ], 200);
    }

    /**
     * Handles Get Products List Request
     *
     * @route '/api/products'
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProducts(Request $request)
    {

        $user = auth()->guard('api')->user();
        $lang = app()->getLocale();

        $perPage = 20;

        if($request->perPage && $request->perPage < 200) {
            $perPage = +($request->perPage);
        }

        $categoriesSearch = [];
        if($request->categories) {
            $categoriesSearch = json_decode($request->categories, true);
        }

        $countriesSearch = [];
        if($request->countries) {
            $countriesSearch = json_decode($request->countries, true);
        }

        $brandsSearch = [];
        if($request->brands) {
            $brandsSearch = json_decode($request->brands, true);
        }

        $productsIds = [];
        if($request->productsIds) {
            $productsIds = json_decode($request->productsIds, true);
        }

        $sort = $request->sort && in_array($request->sort, ['newest', 'cheapest', 'expensive']) ? $request->sort : null;

        $data = Product::with(['categories:id,title'])

                ->where('status', true)

                ->when(count($categoriesSearch) > 0 , function($query) use($categoriesSearch) {
                    $query->whereHas('categories', function($query) use($categoriesSearch) {
                        $query->whereIn('categories.id', $categoriesSearch);
                    });
                })

                ->when(count($countriesSearch) > 0 , function($query) use($countriesSearch) {
                    $query->whereHas('countries', function($query) use($countriesSearch) {
                        $query->whereIn('countries.id', $countriesSearch);
                    });
                })
                
                ->when(count($brandsSearch) > 0 , function($query) use($brandsSearch) {
                    $query->whereHas('brand', function($query) use($brandsSearch) {
                        $query->whereIn('brands.id', $brandsSearch);
                    });
                })
                                
                ->when(count($productsIds) > 0 , function($query) use($productsIds) {
                    $query->whereIn('id', $productsIds);
                })
                
                ->when($request->name, function($query) use($request, $lang){
                    $query->where('name', 'ilike', "%{$request->name}%")
                        ->orWhere("display_name->{$lang}", 'ilike', "%{$request->name}%");
                })
                

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

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'display_name' => $product->display_name,
                    'slug' => slugify($product->display_name ?: $product->name),
                    // 'zone'  => ProductZoneEnum::instanceFromKey($product->zone)->value(),
                    'zone'  => $product->zone,
                    // 'type'  => ProductTypeEnum::instanceFromKey($product->type)->value(),
                    'delivery_type' => 'بلافاصله پس از خرید',
                    'from' => $product->min_price,
                    'to' => $product->max_price,
                    'currency_price' => $currencyName,
                    'price_from' => $formulaItem ? number_format($this->calculation($formulaItem->formula, $product->min_cost, $rate, $formulaGroup->et, $formulaGroup->un, $formulaGroup->pr)) : 0,
                    // 'price_to' => number_format(($product->max_cost * $rate) / 10),
                    'image' => $this->getProductImage($product->image ?: ''),
                    'image_thumbnails' => $product->image ? [
                        '360' => $imageData['name'] . '-360.' . $imageData['format'],
                        '640' => $imageData['name'] . '-640.' . $imageData['format'],
                        '1024' => $imageData['name'] . '-1024.' . $imageData['format'],
                    ] : [],
                    'countries' => $countries,
                    'categories' => $product->categories->map(fn($category): array => [
                        'id'=> $category->id,
                        'title' => $category->title
                    ])->toArray(),
                    'available' => $formulaItem ? $status : false,
                ];
            }),
            'currentPage' => $currentPage,
            'total' => $total,
            'lastPage' => $lastPage,
            'perPage' => $perPage,
        ], 200);
    }

    /**
     * Handles Get Product Detail Request
     *
     * @route '/api/products/{productId}'
     * @param Request $request
     * @param integer $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProduct(Request $request, $productId)
    {
        $user = auth()->guard('api')->user();
        $lang = app()->getLocale();

        $product = Product::with(['variants', 'categories:id,title,status','tags:id,title,status', 'questions' => function($query) {
            $query->where('status', true);
        }])->find($productId);

        if(! $product) {
            return json_response([
                'error' => __("The desired product does not exist!")
            ], 404);
        }

        $currencies = Currency::select('iso', 'currency_name', 'last_price')->whereIn('iso', [$product->cost_currency, $product->currency_price])->get();

        $rate = 1;
        $status = false;
        $faceValueCurrencyName = __("Dollar");

        if(count($currencies) == 2 || $product->cost_currency == $product->currency_price) {

            $cCurrency = $currencies[0]->iso == $product->cost_currency ? $currencies[0] : $currencies[1];
            $faceValueCurrency = $currencies[0]->iso == $product->currency_price ? $currencies[0] : $currencies[1];
            $rate = +($cCurrency->last_price);
            $faceValueCurrencyName = $faceValueCurrency->currency_name;

            if($faceValueCurrency->last_price !== '' && $faceValueCurrency->last_price != 0) {
                $status = true;
            } 

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

        $categories = $product->categories->where('status', true)->map(fn($category): array => [
                    'id'=> $category->id,
                    'title' => $category->title,
                    'slug' => slugify($category->title),
                ])->toArray();

        $tags = $product->tags->where('status', true)->map(fn($tag) => $tag->title)->toArray();

        if($product->categories_tagable) {
            $tags = array_unique(array_merge($tags, collect($categories)->map(fn($category) => $category['title'])->toArray()));
        }

        $variants = [];
        $priceType = $product->price_type;

        if($formulaItem) {

            $multipleType = false;
            $lastType = null;

            foreach($product->variants->where('vendor_id', 2)->sortBy('face_value') as $variant) {
                if($variant->type == 'fixed' || $variant->type == 'single') {

                    if($lastType && ($lastType != 'fixed' && $lastType != 'single')) {
                        $multipleType = true;
                    }

                    $variants[] = [
                        'id' => $variant->id,
                        'type' => $variant->type,
                        'currency' => $faceValueCurrencyName,
                        'price' => number_format($this->calculation($formulaItem->formula, $variant->cost, $rate, $formulaGroup->et, $formulaGroup->un, $formulaGroup->pr)),
                        'title' => $variant->definition ?: "{$variant->face_value} {$faceValueCurrencyName}",
                        'promotion' => $variant->promotion ?: false,
                    ];
                } else {

                    if($lastType && $lastType !== 'range') {
                        $multipleType = true;
                    }

                    $variants[] = [
                        'id' => $variant->id,
                        'type' => $variant->type,
                        'currency' => $faceValueCurrencyName,
                        'range_from' => $variant->face_value, 
                        'range_to' => $variant->max_face_value, 
                        'step' => $variant->face_value_step, 
                        'promotion' => $variant->promotion ?: false,
                    ];
                }

                $lastType = $variant->type;
            }

            if($multipleType) {
                $priceType = 'range';
                $variants = [collect($variants)->where('type', 'range')->last()];
            }

            // ->each(function($variant) use($faceValueCurrencyName, $rate, $formulaItem, $formulaGroup){

            // });
        }

        $imageData = $this->getProductImageName($product->image);

        $vendor = \DB::table('vendors')->where('service_name', 'cysend')->first();

        if($vendor && $vendor->balance < config('vendor.min_balance')) {
            $status = false;
        }

        return json_response([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => slugify($product->display_name ?: $product->name),
                'display_name' => $product->display_name,
                'zone'  => $product->zone,
                // 'zone'  => ProductZoneEnum::instanceFromKey($product->zone)->value(),
                'type'  => ProductTypeEnum::instanceFromKey($product->type)->value(),
                'price_type' => $priceType,
                'delivery_type' => 'بلافاصله پس از خرید',
                'from' => $product->min_price,
                'to' => $product->max_price,
                'currency_price' => $faceValueCurrencyName,
                'price_from' => $formulaItem ? number_format($this->calculation($formulaItem->formula, $product->min_cost, $rate, $formulaGroup->et, $formulaGroup->un, $formulaGroup->pr)) : 0,
                // 'price_to' => number_format(($product->max_cost * $rate) / 10),
                'categories' => $categories,
                'tags' => $tags,
                'countries' => $countries,
                'questions' => $product->questions->map(fn($q): array => [
                    'question' => $q->question,
                    'answer' => $q->answer,
                ])->toArray(),
                'variants' => $status ? $variants : [],
                'image' => $this->getProductImage($product->image ?: ''),
                'image_thumbnails' => $product->image ? [
                    '360' => $imageData['name'] . '-360.' . $imageData['format'],
                    '640' => $imageData['name'] . '-640.' . $imageData['format'],
                    '1024' => $imageData['name'] . '-1024.' . $imageData['format'],
                ] : [],
                'beneficiaryInformation' => $this->getBeneficiaryInformation($lang, $product->beneficiary_information),
                'videos' => $this->getVideos($product->videos_status ? $product->videos : []),
                'introduction' => $product->introduction_status ? $product->introduction : '',
                'application' => $product->application_status ? $product->application : '',
                'usage_method' => $product->usage_method_status ? $product->usage_method : '',
            ],
        ], 200);
    }

    protected function calculation($formula, $price, $ex, $et = '', $un = '', $pr = '')
    {
        $calc = new \App\Currency\Services\CalculatePriceService;
        return $calc->calculation($formula, $price, $ex,  $et, $un, $pr);
    }

    /**
     * Get Beneficiary Information Data
     *
     * @param srting $lang
     * @param $data nullable
     * @return array []
     */
    protected function getBeneficiaryInformation($lang, $data = null)
    {
        if($data && count($data)) {
            $list = [];
            foreach($data as $info) {
                $item = $info['fields'];
                $list[] = [
                    'name' => $item['name'],
                    'type' => $item['type'],
                    'pattern' => $item['pattern'] ?: '',
                    'required' => $item['required'],
                    'description' => $item['description'] && isset($item['description'][$lang]) && $item['description'][$lang] ? $item['description'][$lang] : '',
                    'display_name' => $item['display_name'] && isset($item['display_name'][$lang]) && $item['display_name'][$lang] ? $item['display_name'][$lang] : $item['name'],
                ];
            }
            return $list;
        }
        return [];
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

    /**
     * Get Videos Data
     *
     * @param $data nullable
     * @return array []
     */
    protected function getVideos($data = null)
    {
        if($data && count($data)) {
            $list = [];
            foreach($data as $info) {
                $item = $info['fields'];
                $list[] = [
                    'link' => $item['link'],
                    'cover' => $item['cover'] ? Storage::disk('products.video-covers')->url($item['cover']) : '',
                    'title' => $item['title'],
                ];
            }
            return $list;
        }
        return [];
    }
}
