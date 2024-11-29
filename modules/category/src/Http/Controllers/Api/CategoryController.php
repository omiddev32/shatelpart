<?php

namespace App\Category\Http\Controllers\Api;

use App\Core\Controller;
use Illuminate\Http\Request;
use App\Category\Entities\Category;

class CategoryController extends Controller
{
    public function getCategories(Request $request)
    {
        return json_response([
            'caregories' => Category::select(['id', 'title', 'parent', 'status'])->with('childs')->where(['status' => true, 'parent' => null])
                ->get()->map(fn($category): array => [
                    'id' => $category->id,
                    'title' => $category->title,
                    'slug' => slugify($category->title),
                    'image' => 'fa-solid fa-list',
                    'hasSubItem' => count($category->childs) > 0,
                    'subItems' => $category->childs->where('status', true)->map(fn($sub): array => [
                        'id' => $sub->id,
                        'title' => $sub->title,
                        'slug' => slugify($category->title),
                        'hasSubItem' => false,
                    ])->toArray()
                ])->toArray()
        ], 200);
    }
}
