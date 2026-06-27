<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Admin\Models\ColorTag;
use Modules\ProductManagement\Models\VariantAttribute;

class AttributeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $attributes = VariantAttribute::all();
            
            foreach ($attributes as $attribute) {
                if($attribute->type_values != null && $attribute->type == 'select') {
                    $attribute->type_values = explode(',', $attribute->type_values);
                }
            }
            
            return response()->json([
                'status' => true,
                'data' => $attributes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getColorTags(){
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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('admin::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('admin::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
