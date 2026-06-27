<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Modules\Admin\Models\ColorTag;
use Modules\ProductManagement\Models\Category;
use Modules\ProductManagement\Models\Product;
use Modules\ProductManagement\Models\VariantAttribute;
use Modules\Vendor\Models\Vendor;
use Illuminate\Support\Str;
use Modules\ProductManagement\Models\ProductImage;
use Modules\ProductManagement\Models\ProductVariant;
use Modules\ProductManagement\Models\VariantValue;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with(['category', 'vendor', 'images', 'variants'])
            ->latest()
            ->paginate(20);

        return view('admin::products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::whereNull('parent_id')->with('children')->get();
        $vendors = Vendor::where('status', 'Active')->get();
        $variantAttributes = VariantAttribute::all();
        $colorTags = ColorTag::all();

        return view('admin::products.create', compact(
            'categories', 
            'vendors', 
            'variantAttributes', 
            'colorTags'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_arabic' => 'required|string|max:255',
            'name_german' => 'required|string|max:255',
            'description_arabic' => 'nullable|string',
            'description_german' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'child_category_id' => 'nullable|exists:categories,id',
            'vendor_id' => 'required|exists:vendors,id',
            'weight' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'variants' => 'required|array|min:1',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.cost' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'required|integer|min:0',
            'variants.*.tax' => 'nullable|integer|min:0|max:100',
            'variants.*.expiry_date' => 'nullable|date',
            'variants.*.expiry_days' => 'nullable|integer|min:0',
            'variants.*.color_tag_id' => 'nullable|exists:color_tags,id',
            'variants.*.attributes' => 'required|array|min:1',
            'product_images' => 'required|array|min:1',
            'product_images.*.image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'product_images.*.color' => 'nullable|string',
            'size_chart' => 'nullable|array',
            'size_chart.columns' => 'nullable|array',
            'size_chart.columns.*' => 'required|string|max:50',
            'size_chart.rows' => 'nullable|array',
            'size_chart.rows.*.size' => 'required|string|max:50',
            'size_chart.rows.*.values' => 'nullable|array',
            'size_chart.rows.*.values.*' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }


        try {
            DB::beginTransaction();

            // Create product
            $product = Product::create([
                'name_arabic' => $request->name_arabic,
                'name_german' => $request->name_german,
                'description_arabic' => $request->description_arabic,
                'description_german' => $request->description_german,
                'slug_arabic' => Str::slug($request->name_arabic) . '-' . Str::random(5),
                'slug_german' => Str::slug($request->name_german) . '-' . Str::random(5),
                'category_id' => $request->category_id,
                'child_category_id' => $request->child_category_id,
                'vendor_id' => $request->vendor_id,
                'weight' => $request->weight ?? 0,
                'is_active' => $request->boolean('is_active', true),
            ]);

            $product->size_chart = $this->normalizeSizeChart($request->input('size_chart'));
            $product->save();

            // Handle product images
            if ($request->has('product_images')) {
                foreach ($request->all()['product_images'] as $index => $imageData) {
                    if (isset($imageData['image'])) {
                        $path = $imageData['image']->store('products/images', 'public');
                        $color = $request->input("product_images.{$index}.color");
                        
                        ProductImage::create([
                            'product_id' => $product->id,
                            'image' => $path,
                            'color' => $color,
                        ]);
                    }
                }
            }

            // Create variants
            foreach ($request->variants as $variantIndex => $variantData) {
                // Generate unique SKU
                $sku = $this->generateSKU($product->id, $variantIndex, $variantData);
                
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'price' => $variantData['price'],
                    'cost' => $variantData['cost'] ?? 0,
                    'stock' => $variantData['stock'],
                    'tax' => $variantData['tax'] ?? 0,
                    'sku' => $sku,
                    'expiry_date' => $variantData['expiry_date'] ?? null,
                    'expiry_days' => $variantData['expiry_days'] ?? null,
                    'color_tag_id' => $variantData['color_tag_id'] ?? null,
                    'is_active' => true,
                    'is_out_of_stock' => $variantData['stock'] <= 0,
                ]);

                // Create variant values (attributes)
                foreach ($variantData['attributes'] as $attrIndex => $attribute) {
                    $variantAttribute = VariantAttribute::find($attribute['attribute_id']);
                    
                    $value = $attribute['value'];
                    
                    // Handle pattern uploads
                    if ($variantAttribute->type === 'pattern' && $request->hasFile("variants.{$variantIndex}.attributes.{$attrIndex}.value")) {
                        $patternFile = $request->file("variants.{$variantIndex}.attributes.{$attrIndex}.value");
                        $value = $patternFile->store('products/images/patterns', 'public');
                    }
                    
                    // Handle color format (convert hex to 0xFF format if needed)
                    if ($variantAttribute->type === 'color' && !str_starts_with($value, '0x')) {
                        $value = $this->hexToFlutterColor($value);
                    }

                    VariantValue::create([
                        'product_variants_id' => $variant->id,
                        'variant_attribute_id' => $attribute['attribute_id'],
                        'value' => $value,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully!',
                'redirect' => route('products.show', $product->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate unique SKU for variant
     */
    private function generateSKU($productId, $variantIndex, $variantData): string
    {
        $prefix = strtoupper(Str::random(3));
        $productPart = str_pad($productId, 4, '0', STR_PAD_LEFT);
        $variantPart = str_pad($variantIndex + 1, 2, '0', STR_PAD_LEFT);
        
        // Add size or first attribute if available
        $attrPart = '';
        if (!empty($variantData['attributes'])) {
            $firstAttr = $variantData['attributes'][0]['value'] ?? '';
            if (is_string($firstAttr)) {
                $attrPart = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $firstAttr), 0, 4));
            }
        }
        
        return "{$prefix}-{$productPart}-{$variantPart}-{$attrPart}";
    }

    /**
     * Convert hex color to Flutter format
     */
    private function hexToFlutterColor($hex): string
    {
        $hex = ltrim($hex, '#');
        
        if (strlen($hex) === 6) {
            return '0xFF' . strtoupper($hex);
        } elseif (strlen($hex) === 8) {
            return '0x' . strtoupper($hex);
        }
        
        return '0xFF' . strtoupper($hex);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $product = Product::with([
            'variants.variantValues.variantAttribute', 
            'images', 
            'variants.colorTag', 
            'category.parent', 
            'vendor'
        ])->findOrFail($id);

        return view('admin::products.show', compact('product'));
    }

    /**
     * Normalize and validate the size chart input.
     * Forces unit to 'cm', casts values to float, and returns null when empty.
     */
    private function normalizeSizeChart(?array $input): ?array
    {
        if (!$input) {
            return null;
        }
        $columns = array_values(array_unique(array_filter(
            array_map('trim', $input['columns'] ?? [])
        )));
        $rows = [];
        foreach ($input['rows'] ?? [] as $row) {
            $size = trim($row['size'] ?? '');
            if ($size === '') {
                continue;
            }
            $values = [];
            foreach ($columns as $col) {
                $v = $row['values'][$col] ?? null;
                $values[$col] = ($v === null || $v === '') ? null : (float) $v;
            }
            $rows[] = ['size' => $size, 'values' => $values];
        }
        if (empty($columns) || empty($rows)) {
            return null;
        }
        return ['unit' => 'cm', 'columns' => $columns, 'rows' => $rows];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $product = Product::with([
            'variants.variantValues.variantAttribute',
            'images',
            'variants.colorTag'
        ])->findOrFail($id);
        
        $categories = Category::whereNull('parent_id')->with('children')->get();
        $vendors = Vendor::where('status', "Active")->get();
        $variantAttributes = VariantAttribute::all();
        $colorTags = ColorTag::all();

        return view('admin::products.edit', compact(
            'product',
            'categories', 
            'vendors', 
            'variantAttributes', 
            'colorTags'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name_arabic' => 'sometimes|required|string|max:255',
            'name_german' => 'sometimes|required|string|max:255',
            'description_arabic' => 'nullable|string',
            'description_german' => 'nullable|string',
            'category_id' => 'sometimes|required|exists:categories,id',
            'child_category_id' => 'nullable|exists:categories,id',
            'vendor_id' => 'sometimes|required|exists:vendors,id',
            'weight' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'existing_variants' => 'nullable|array',
            'existing_variants.*.price' => 'nullable|numeric|min:0',
            'existing_variants.*.cost' => 'nullable|numeric|min:0',
            'existing_variants.*.stock' => 'nullable|integer|min:0',
            'existing_variants.*.tax' => 'nullable|integer|min:0|max:100',
            'existing_variants.*.expiry_date' => 'nullable|date',
            'existing_variants.*.expiry_days' => 'nullable|integer|min:0',
            'new_variants' => 'nullable|array',
            'new_variants.*.price' => 'required_with:new_variants|numeric|min:0',
            'new_variants.*.stock' => 'required_with:new_variants|integer|min:0',
            'new_variants.*.attributes' => 'nullable|array',
            'new_images' => 'nullable|array',
            'new_images.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'size_chart' => 'nullable|array',
            'size_chart.columns' => 'nullable|array',
            'size_chart.columns.*' => 'required|string|max:50',
            'size_chart.rows' => 'nullable|array',
            'size_chart.rows.*.size' => 'required|string|max:50',
            'size_chart.rows.*.values' => 'nullable|array',
            'size_chart.rows.*.values.*' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $product = Product::findOrFail($id);

            $product->fill(array_filter([
                'name_arabic' => $request->name_arabic,
                'name_german' => $request->name_german,
                'description_arabic' => $request->description_arabic,
                'description_german' => $request->description_german,
                'category_id' => $request->category_id,
                'child_category_id' => $request->child_category_id,
                'vendor_id' => $request->vendor_id,
                'weight' => $request->weight,
                'is_active' => $request->has('is_active') ? $request->boolean('is_active') : null,
            ], fn($v) => $v !== null));

            $product->size_chart = $this->normalizeSizeChart($request->input('size_chart'));
            $product->save();

            // Update existing variants (keyed by variant id)
            foreach ((array) $request->input('existing_variants', []) as $variantId => $data) {
                $variant = ProductVariant::where('product_id', $product->id)->find($variantId);
                if (!$variant) {
                    continue;
                }
                if (isset($data['price']) && $data['price'] !== '') {
                    $variant->price = $data['price'];
                }
                if (isset($data['cost']) && $data['cost'] !== '') {
                    $variant->cost = $data['cost'];
                }
                if (isset($data['tax']) && $data['tax'] !== '') {
                    $variant->tax = $data['tax'];
                }
                if (array_key_exists('expiry_date', $data)) {
                    $variant->expiry_date = $data['expiry_date'] !== '' ? $data['expiry_date'] : null;
                }
                if (array_key_exists('expiry_days', $data)) {
                    $variant->expiry_days = $data['expiry_days'] !== '' ? $data['expiry_days'] : null;
                }
                if (isset($data['stock']) && $data['stock'] !== '') {
                    $variant->stock = $data['stock'];
                    $variant->is_out_of_stock = $data['stock'] <= 0;
                }
                if (isset($data['is_active'])) {
                    $variant->is_active = (bool) $data['is_active'];
                }
                $variant->save();
            }

            // Delete removed variants (+ their attribute values)
            $deleteVariants = json_decode($request->input('delete_variants', '[]'), true) ?: [];
            foreach ($deleteVariants as $vId) {
                $variant = ProductVariant::where('product_id', $product->id)->find($vId);
                if ($variant) {
                    VariantValue::where('product_variants_id', $variant->id)->delete();
                    $variant->delete();
                }
            }

            // Add new variants (mirrors store())
            foreach ((array) $request->input('new_variants', []) as $variantIndex => $variantData) {
                $sku = $this->generateSKU($product->id, $variantIndex, $variantData);
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'price' => $variantData['price'],
                    'cost' => $variantData['cost'] ?? 0,
                    'stock' => $variantData['stock'],
                    'tax' => $variantData['tax'] ?? 0,
                    'sku' => $sku,
                    'expiry_date' => $variantData['expiry_date'] ?? null,
                    'expiry_days' => $variantData['expiry_days'] ?? null,
                    'color_tag_id' => $variantData['color_tag_id'] ?? null,
                    'is_active' => true,
                    'is_out_of_stock' => ($variantData['stock'] ?? 0) <= 0,
                ]);

                foreach (($variantData['attributes'] ?? []) as $attrIndex => $attribute) {
                    $variantAttribute = VariantAttribute::find($attribute['attribute_id'] ?? null);
                    if (!$variantAttribute) {
                        continue;
                    }
                    $value = $attribute['value'] ?? null;
                    // Pattern image upload (edit form sends it as pattern_file)
                    foreach (['pattern_file', 'value'] as $fileKey) {
                        if ($request->hasFile("new_variants.{$variantIndex}.attributes.{$attrIndex}.{$fileKey}")) {
                            $value = $request->file("new_variants.{$variantIndex}.attributes.{$attrIndex}.{$fileKey}")
                                ->store('products/images/patterns', 'public');
                            break;
                        }
                    }
                    if ($variantAttribute->type === 'color' && is_string($value) && $value !== '' && !str_starts_with($value, '0x')) {
                        $value = $this->hexToFlutterColor($value);
                    }
                    VariantValue::create([
                        'product_variants_id' => $variant->id,
                        'variant_attribute_id' => $attribute['attribute_id'],
                        'value' => $value,
                    ]);
                }
            }

            // Delete removed images (+ files)
            $deleteImages = json_decode($request->input('delete_images', '[]'), true) ?: [];
            foreach ($deleteImages as $imgId) {
                // Bypass the 'image_url' global scope so $img->image is the raw
                // storage path (the scope rewrites it to a full URL).
                $img = ProductImage::withoutGlobalScope('image_url')
                    ->where('product_id', $product->id)->find($imgId);
                if ($img) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($img->image);
                    $img->delete();
                }
            }

            // Add new images
            foreach ((array) $request->input('new_images', []) as $index => $imageData) {
                if ($request->hasFile("new_images.{$index}.image")) {
                    $path = $request->file("new_images.{$index}.image")->store('products/images', 'public');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image' => $path,
                        'color' => $request->input("new_images.{$index}.color"),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully!',
                'redirect' => route('products.show', $product->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Admin product update failed', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? 'Failed to update product: ' . $e->getMessage() : 'Failed to update product.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            
            // Delete images from storage
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->image);
            }
            
            // Delete pattern images
            foreach ($product->variants as $variant) {
                foreach ($variant->variantValues as $value) {
                    if ($value->variantAttribute->type === 'pattern') {
                        Storage::disk('public')->delete($value->value);
                    }
                }
            }
            
            $product->delete();

            return redirect()->route('products.index')
                ->with('success', 'Product deleted successfully!');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete product: ' . $e->getMessage());
        }
    }
}