<?php 

namespace Modules\ProductManagement\Http\Controllers\V1\Api;

use Modules\ProductManagement\Models\Category;

class CategoryController {
    public function index(){
        $categories = Category::where('parent_id', null)->get();
        $categories->each(function($category) {
            $category->sub_categories = Category::where('parent_id', $category->id)->get();
        });

        return response()->json([
            'status' => true,
            'data' => $categories
        ]);
    }
}