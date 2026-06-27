<?php 


namespace Modules\Admin\Http\Controllers;

use Modules\Admin\Models\ColorTag;

class TagController {
    public function getColorTagsApi(){
        try {
            $colorTags = ColorTag::all();

            return response()->json([
            'status' => true,
            'data' => $colorTags
            ]);
        } catch (\Exception $e) {
            return response()->json([
            'status' => false,
            'message' => $e->getMessage()
            ], 500);
        }
    }
}