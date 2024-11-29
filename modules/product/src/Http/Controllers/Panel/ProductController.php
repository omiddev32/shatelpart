<?php

namespace App\Product\Http\Controllers\Panel;

use App\Core\Controller;
use Illuminate\Http\Request;
use App\Product\Entities\Product;

class ProductController extends Controller
{
    public function providedProducts(Request $request)
    {   
        if(! auth()->user()->hasPermission('view.any.products')) {
            return json_response([], 403);
        }
        $search = $request->search;

        return Product::select(['id', 'name', 'display_name'])
          ->where(function($query) use($search){
            $query->where('name', 'ilike', "%{$search}%");
            foreach(array_keys(config('translatable.locales')) as $lang) {
                $query->orWhere("display_name->{$lang}", 'ilike', "%{$search}%");
            }
          })
          ->has('vendors', '<', 2)
          ->get()
          ->map(fn($product): array => [
            'id' => $product->id,
            'name' => $product->display_name ?: $product->name,
          ])
          ->toArray();
    }
}
