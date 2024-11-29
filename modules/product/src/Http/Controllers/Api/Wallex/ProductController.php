<?php

namespace App\Product\Http\Controllers\Api\Wallex;

use App\Core\Controller;
use Illuminate\Http\Request;
use App\Vendor\Services\VendorService;
use App\Country\Entities\Country;

class ProductController extends Controller
{   
    private $countriesList = [];

    public function getProducts()
    {
        $this->countriesList = \DB::table('countries')->select('id', 'name')->get()->map(fn($country): array => [
            'id' => $country->id,
            'name' => json_decode($country->name)->fa,
        ])->toArray();

        $service = new VendorService('wallex');
        $result = $service->proccess('getProducts');

        if($result->total > 0) {
            $count = 0;
            $products = [];
            $faceValues = [];

            foreach($result->data as $data) {
                if(property_exists($data, 'variants')) {
                    $variants = [];
                    $country = $this->findCountry($data->name);
                    // $countries = [];
                    // if(count($country)) {
                    //     $countries[] = $country['id'];
                    // }

                    $importAccess = true;

                    foreach($data->variants as $variant) {
                        if($importAccess) {
                            $variants[] = $variant->id;
                            $faceValue = preg_replace('/[^0-9]/', '', faTOen($variant->title));
                            if($faceValue) {
                                $faceValues[] = [
                                    'product_id' => $data->product_id,
                                    'vendor_id' => 3,
                                    'face_value_id' => $variant->id,
                                    'type' => 'fixed',
                                    'face_value_currency' => $variant->currency,
                                    'face_value' => $faceValue,
                                    'definition' => "",
                                    'cost_currency' => "IRR",
                                    'cost' => $variant->price * 10,
                                    'promotion' => false,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            } else {
                                $importAccess = false;
                            }
                        }
                    }
                    if($importAccess) {
                        $products[] = [
                            'vendor_id' => 3,
                            'zone' => (isset($country['name']) && $country['name'] === 'اروپا') ? 'Eurozone' : 'Others',
                            'product_id' => $data->product_id,
                            'name' => $data->name,
                            'type' => 'prepaid_code',
                            'logo_url' => $data->image,
                            'description' => json_encode([], true),
                            'beneficiary_information' => json_encode([], true),
                            'usage_instructions' => json_encode([], true),
                            'countries' => json_encode([], true),
                            'face_values' => json_encode($variants, true),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];

                        $count += 1;
                    }
                }

            }

            \DB::table('product_apis')->insert($products);
            \DB::table('face_value_apis')->insert($faceValues);
      
            // \DB::table('product_apis')->upsert($products, ['vendor_id', 'product_id'], ['name', 'face_values']);
            // \DB::table('face_value_apis')->upsert($faceValues, ['vendor_id', 'product_id', 'face_value_id'], ['face_value', 'cost']);



            \DB::table('vendors')->where('id', 3)->update([
                'number_of_products_is_not_provided' => $count,
                'latest_product_updates' => now(),
            ]);

        }

        return json_response([
            'message' => 'successfull'
        ], 200);

    }

    public function findCountry($name)
    {
        foreach($this->countriesList as $country) {
            if(str_contains($name, $country['name'])) {
                return $country;
            }
        }

        return [];
    }
}
