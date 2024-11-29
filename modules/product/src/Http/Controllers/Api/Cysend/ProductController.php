<?php

namespace App\Product\Http\Controllers\Api\Cysend;

use App\Core\Controller;
use Illuminate\Http\Request;
use App\Vendor\Services\VendorService;
use Storage;

class ProductController extends Controller
{
    public function getProducts(Request $request)
    {
        // $json = json_decode(file_get_contents(str_replace('//', '/', module_path('product', 'products.json'))), true);
        $service = new VendorService('cysend');
        $products = $service->getProducts();
        // $products = $service->proccess('getProducts');
        $productCount = count($products);
        dd(collect($products)->chunk(200)->toArray());

        // foreach(collect($products)->chunk(200)->toArray() as $group) {

        //     \DB::table('product_apis')->insert(
        //         collect($group)->map(fn($product): array => [
        //             'vendor_id' => 2,
        //             'zone' => $product->country_zone,
        //             'product_id' => $product->product_id,
        //             'name' => $product->product_name,
        //             'description' => json_encode($product->product_description, true),
        //             'logo_url' => "https://www.cysend.com" . $product->logo_url,
        //             'type' => $product->type,
        //             'promotion' => $product->promotion,
        //             'maintenance' => $product->maintenance,
        //             'beneficiary_information' => json_encode($product->beneficiary_information, true),
        //             'usage_instructions' => json_encode(isset($product->usage_instructions) ? $product->usage_instructions : [], true),
        //             'face_values' => json_encode($product->face_value_ids, true),
        //             'countries' => json_encode($product->countries, true),
        //             // 'countries' => json_encode(($product['country_zone'] == 'Global' ? [] : $product['countries']), true),
        //             'created_at' => now(),
        //             'updated_at' => now(),
        //         ])->toArray()
        //     );
        // }

        // \DB::table('vendors')->where('id', 2)->update([
        //     'number_of_products_is_not_provided' => $productCount,
        //     'latest_product_updates' => now(),
        // ]);

        return json_response([], 200);
    }
}
