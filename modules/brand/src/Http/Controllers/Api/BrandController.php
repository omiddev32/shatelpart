<?php

namespace App\Brand\Http\Controllers\Api;

use App\Core\Controller;
use Illuminate\Http\Request;
use App\Brand\Entities\Brand;
use Storage;

class BrandController extends Controller
{
    public function getBrands(Request $request)
    {
        return json_response([
            'brands' => Brand::select(['id', 'name', 'image', 'status'])
                ->where(['status' => true])
                ->has('products')
                ->get()->map(fn($brand): array => [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'image' => $brand->image ? Storage::disk('brands')->url($brand->image) : '',
                ])->toArray()
        ], 200);
    }
}
