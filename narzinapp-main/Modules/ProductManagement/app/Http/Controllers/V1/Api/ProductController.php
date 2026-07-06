<?php

namespace Modules\ProductManagement\Http\Controllers\V1\Api;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\Admin\Models\PriceExchange;
use Modules\ProductManagement\Models\Category;
use Modules\ProductManagement\Models\Product;
use Modules\ProductManagement\Models\ProductImage;
use Modules\ProductManagement\Models\ProductVariant;
use Modules\ProductManagement\Models\VariantAttribute;

class ProductController extends Controller
{
    /**
     * Securely decode and store a base64 data-URI image.
     *
     * Only real raster images with a whitelisted extension are accepted. This
     * prevents an attacker from smuggling an executable extension (e.g.
     * "data:image/php;base64,...") into web-served storage, which would
     * otherwise allow arbitrary file write / remote code execution.
     */
    private function storeBase64Image(string $dataUri, string $directory, string $prefix): string
    {
        if (!preg_match('#^data:image/(jpeg|jpg|png|webp|gif);base64,#i', $dataUri, $m)) {
            throw new \Exception('Invalid image format. Only JPEG, PNG, WEBP or GIF images are allowed.');
        }

        $extension = strtolower($m[1]) === 'jpeg' ? 'jpg' : strtolower($m[1]);

        $decoded = base64_decode(substr($dataUri, strpos($dataUri, ',') + 1), true);
        if ($decoded === false) {
            throw new \Exception('Unable to decode the base64 image.');
        }

        // Confirm the decoded bytes are actually an image, not a disguised payload.
        if (@getimagesizefromstring($decoded) === false) {
            throw new \Exception('Uploaded data is not a valid image.');
        }

        if (strlen($decoded) > 2 * 1024 * 1024) {
            throw new \Exception('Image exceeds the maximum allowed size of 2MB.');
        }

        $path = trim($directory, '/') . '/' . $prefix . '_' . time() . '_' . uniqid() . '.' . $extension;
        Storage::disk('public')->put($path, $decoded);

        return $path;
    }

    public function index(Request $request)
    {

        try {
            $latestExchange = PriceExchange::latest('created_at')->first();
            $latestRate = $latestExchange->price_rate ?? 1;
            $globalMarkup = \Modules\Admin\Models\PlatformMarkup::getLatest();


            $query = Product::with(['images', 'category', 'vendor'])
                ->select('products.*')
                ->addSelect([
                    'min_price' => ProductVariant::selectRaw('price / ?', [$latestRate])
                        ->whereColumn('product_id', 'products.id')
                        ->where('is_active', true)
                        ->where('is_out_of_stock', false)
                        ->orderBy('price', 'asc')
                        ->limit(1),
                    'min_price_variant_id' => ProductVariant::select('id')
                        ->whereColumn('product_id', 'products.id')
                        ->where('is_active', true)
                        ->where('is_out_of_stock', false)
                        ->orderBy('price', 'asc')
                        ->limit(1)
                ]);

            // Add search functionality
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name_arabic', 'like', "%{$search}%")
                        ->orWhere('name_german', 'like', "%{$search}%")
                        ->orWhere('description_arabic', 'like', "%{$search}%")
                        ->orWhere('description_german', 'like', "%{$search}%");
                });
            }

            // Add category filter
            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }



            // Add sorting
            $sortField = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            $allowedSortFields = ['created_at', 'name_arabic', 'name_german', 'min_price'];

            if (in_array($sortField, $allowedSortFields)) {
                $query->orderBy($sortField, $sortDirection);
            }

            $products = $query->paginate($request->get('per_page', 15));

            // Apply markup to min_price
            $products->getCollection()->transform(function ($product) use ($globalMarkup) {
                $vendor = $product->vendor;
                $markup = ($vendor && $vendor->markup_percentage !== null)
                    ? (float) $vendor->markup_percentage
                    : (float) $globalMarkup;
                if ($product->min_price) {
                    $product->min_price = round($product->min_price * (1 + $markup / 100), 2);
                }
                unset($product->vendor); // Don't expose vendor markup to customers
                return $product;
            });

            return response()->json([
                'status' => true,
                'data' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }



    public function getProductsByVendorId($vendorId)
    {
        try {
            $latestExchange = PriceExchange::latest('created_at')->first();
            $latestRate = $latestExchange->price_rate ?? 1;
            $globalMarkup = \Modules\Admin\Models\PlatformMarkup::getLatest();

            $products = Product::with(['images', 'category', 'vendor'])
                ->select('products.*')
                ->addSelect([
                    'min_price' => ProductVariant::selectRaw('price / ?', [$latestRate])
                        ->whereColumn('product_id', 'products.id')
                        ->where('is_active', true)
                        ->where('is_out_of_stock', false)
                        ->orderBy('price', 'asc')
                        ->limit(1),
                    'min_price_variant_id' => ProductVariant::select('id')
                        ->whereColumn('product_id', 'products.id')
                        ->where('is_active', true)
                        ->where('is_out_of_stock', false)
                        ->orderBy('price', 'asc')
                        ->limit(1)
                ])
                ->where('vendor_id', $vendorId)
                ->get();

            // Apply markup to min_price
            $products->transform(function ($product) use ($globalMarkup) {
                $vendor = $product->vendor;
                $markup = ($vendor && $vendor->markup_percentage !== null)
                    ? (float) $vendor->markup_percentage
                    : (float) $globalMarkup;
                if ($product->min_price) {
                    $product->min_price = round($product->min_price * (1 + $markup / 100), 2);
                }
                unset($product->vendor);
                return $product;
            });

            return response()->json([
                'status' => true,
                'data' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }




    public function show($id)
    {
        try {
            $latestExchange = PriceExchange::latest('created_at')->first();
            $latestRate = $latestExchange->price_rate ?? 1;
            $globalMarkup = \Modules\Admin\Models\PlatformMarkup::getLatest();

            $product = Product::with([
                'variants' => function ($query) {
                    $query->with(['variantValues.variantAttribute']);
                },
                'images',
                'category',
                'vendor',
            ])->find($id);

            if (!$product) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            // Determine markup for this product's vendor
            $vendor = $product->vendor;
            $markup = ($vendor && $vendor->markup_percentage !== null)
                ? (float) $vendor->markup_percentage
                : (float) $globalMarkup;

            // Adjust price for each variant
            $product->variants->transform(function ($variant) use ($latestRate, $markup) {
                $markedUpPrice = $variant->price * (1 + $markup / 100);
                $formattedVariant = [
                    'id' => $variant->id,
                    'price' => round($markedUpPrice / $latestRate, 2),
                    'stock' => $variant->stock,
                    'color_tag_id' => $variant->color_tag_id,
                    'sku' => $variant->sku,
                    'is_active' => $variant->is_active,
                    'is_out_of_stock' => $variant->is_out_of_stock,
                    'expiry_date' => $variant->expiry_date,
                    'expiry_days' => $variant->expiry_days,
                    'attributes' => []
                ];

                foreach ($variant->variantValues as $value) {
                    $formattedVariant['attributes'][] = [
                        'attribute_id' => $value->variantAttribute->id,
                        'name_arabic' => $value->variantAttribute->name_arabic,
                        'type' => $value->variantAttribute->type,
                        'type_values' => $value->variantAttribute->type_values,
                        'name_german' => $value->variantAttribute->name_german,
                        'value' => $value->value
                    ];
                }

                return $formattedVariant;
            });

            return response()->json([
                'status' => true,
                'data' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }




    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name_arabic' => 'required|string|max:255',
                'name_german' => 'required|string|max:255',
                'description_arabic' => 'nullable|string',
                'description_german' => 'nullable|string',
                'category_id' => 'required|exists:categories,id',
                'variants' => 'required|array|min:1',
                'variants.*.price' => 'required|numeric|min:0',
                'variants.*.cost' => 'required|numeric|min:0',
                'variants.*.stock' => 'required|integer|min:0',
                'variants.*.tax' => 'required|integer|min:0',
                'variants.*.color_tag_id' => 'required|exists:color_tags,id',
                'variants.*.expiry_date' => 'nullable|date',
                'variants.*.expiry_days' => 'nullable|integer',
                'variants.*.attributes' => 'required|array',
                'variants.*.attributes.*.attribute_id' => 'required|exists:variant_attributes,id',
                'variants.*.attributes.*.value' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Create product
            $string = Str::random(3);
            $product = Product::create([
                'name_arabic' => $request->name_arabic,
                'name_german' => $request->name_german,
                'slug_arabic' => Str::slug($request->name_arabic) . '-' . $string,
                'slug_german' => Str::slug($request->name_german) . '-' . $string,
                'description_arabic' => $request->description_arabic,
                'description_german' => $request->description_german,
                'category_id' => $request->category_id,
                'vendor_id' => $request->current_vendor->id,
                'is_active' => true
            ]);


            // Create variants with auto-generated SKUs
            foreach ($request->variants as $variantData) {
                $sku = $this->generateSku($product, $variantData);
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'price' => $variantData['price'],
                    'cost' => $variantData['cost'],
                    'stock' => $variantData['stock'],
                    'tax' => $variantData['tax'],
                    'sku' => $sku,
                    'expiry_date' => $variantData['expiry_date'] ?? null,
                    'color_tag_id' => $variantData['color_tag_id'] ?? null,
                    'expiry_days' => $variantData['expiry_days'] ?? null,
                    'is_active' => true,
                    'is_out_of_stock' => $variantData['stock'] <= 0
                ]);

                foreach ($variantData['attributes'] as $attrData) {
                    $attributeModel = VariantAttribute::find($attrData['attribute_id']);
                    if (!$attributeModel) {
                        throw new \Exception('Invalid attribute provided');
                    }

                    if (
                        $attributeModel->type == 'select' && $attributeModel->type_values != null
                        && $attributeModel->type_values != ''
                    ) {
                        $checkArray = array_map('trim', explode(',', $attributeModel->type_values));
                        if (!in_array($attrData['value'], $checkArray)) {
                            throw new \Exception('Invalid attribute value provided');
                        }
                    }

                    if ($attributeModel->type == 'pattern') {
                        // Assuming the uploaded file is in $attrData['value']
                        // $path = $attrData['value']->store('products/images/patterns', 'public');
                        $path = $this->storeBase64Image($attrData['value'], 'products/images/patterns', 'patterns');
                        $variant->variantValues()->create([
                            'variant_attribute_id' => $attrData['attribute_id'],
                            'value' => $path
                        ]);
                    } else {
                        $variant->variantValues()->create([
                            'variant_attribute_id' => $attrData['attribute_id'],
                            'value' => $attrData['value']
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Product created successfully',
                'data' => Product::with(['variants.variantValues', 'images'])->find($product->id)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function addProductImages(Request $request, $id)
    {
        try {
            // We'll still validate 'images.*.image' but we'll check its type later
            $validator = Validator::make($request->all(), [
                'images' => 'required|array',
                'images.*.image' => 'required',
                'images.*.color' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $product = Product::findOrFail($id);

            if ($request->has('images')) {
                foreach ($request->images as $imageData) {
                    // Check if the image is base64 string
                    if (is_string($imageData['image']) && str_starts_with($imageData['image'], 'data:')) {
                        // Handle base64 image (extension whitelisted, bytes verified)
                        $path = $this->storeBase64Image($imageData['image'], 'products/images', 'product');
                    }
                    // Check if it's an uploaded file
                    elseif ($imageData['image'] instanceof \Illuminate\Http\UploadedFile) {
                        // Validate the file as an image
                        $fileValidator = Validator::make(['file' => $imageData['image']], [
                            'file' => 'image|mimes:jpeg,png,jpg|max:2048',
                        ]);

                        if ($fileValidator->fails()) {
                            return response()->json([
                                'status' => false,
                                'message' => 'Invalid image file',
                                'errors' => $fileValidator->errors()
                            ], 422);
                        }

                        // Handle file upload
                        $path = $imageData['image']->store('products/images', 'public');
                    } else {
                        throw new \Exception('Invalid image format. Please provide a valid image file or base64 encoded string.');
                    }

                    // Create the product image record
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image' => $path,
                        'color' => $imageData['color']
                    ]);
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Images added successfully',
                'data' => Product::with('images')->find($product->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name_arabic' => 'sometimes|required|string|max:255',
                'name_german' => 'sometimes|required|string|max:255',
                'description_arabic' => 'nullable|string',
                'description_german' => 'nullable|string',
                'category_id' => 'sometimes|required|exists:categories,id',
                'is_active' => 'sometimes|required|boolean',
                'variants' => 'sometimes|array',
                'variants.*.id' => 'required|exists:product_variants,id',
                'variants.*.price' => 'required|numeric|min:0',
                'variants.*.cost' => 'required|numeric|min:0',
                'variants.*.stock' => 'required|integer|min:0',
                'variants.*.is_active' => 'boolean',
                'new_variants' => 'nullable|array',
                'new_variants.*.price' => 'required|numeric|min:0',
                'new_variants.*.stock' => 'required|integer|min:0',
                'new_variants.*.attributes' => 'required|array',
                'new_variants.*.attributes.*.attribute_id' => 'required|exists:variant_attributes,id',
                'new_variants.*.attributes.*.value' => 'required|string',
                'delete_variants' => 'nullable|array',
                'delete_variants.*' => 'exists:product_variants,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $product = Product::findOrFail($id);

            // Update basic information
            if ($request->has(['name_arabic', 'name_german'])) {
                $product->update([
                    'name_arabic' => $request->name_arabic,
                    'name_german' => $request->name_german,
                    'slug_arabic' => Str::slug($request->name_arabic),
                    'slug_german' => Str::slug($request->name_german),
                    'description_arabic' => $request->description_arabic,
                    'description_german' => $request->description_german,
                    'category_id' => $request->category_id,
                    'is_active' => $request->is_active ?? $product->is_active
                ]);
            }


            // Handle new images
            if ($request->hasFile('new_images')) {
                foreach ($request->file('new_images') as $image) {
                    $path = $image->store('products/images', 'public');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image' => $path,

                    ]);
                }
            }

            if ($request->has('variants')) {
                foreach ($request->variants as $variantData) {
                    $variant = ProductVariant::find($variantData['id']);
                    if ($variant && $variant->product_id === $product->id) {
                        $variant->update([
                            'price' => $variantData['price'],
                            'cost' => $variantData['cost'],
                            'stock' => $variantData['stock'],
                            'is_active' => $variantData['is_active'] ?? $variant->is_active,
                            'is_out_of_stock' => $variantData['stock'] <= 0,
                            'expiry_date' => $variantData['expiry_date'] ?? $variant->expiry_date,
                            'expiry_days' => $variantData['expiry_days'] ?? $variant->expiry_days
                        ]);

                        // Update variant attributes if provided
                        if (isset($variantData['attributes'])) {
                            // Delete existing attribute values
                            $variant->variantValues()->delete();

                            // Create new attribute values
                            foreach ($variantData['attributes'] as $attribute) {
                                $attributeModel = VariantAttribute::find($attribute['attribute_id']);
                                if (!$attributeModel) {
                                    throw new \Exception('Invalid attribute provided');
                                }

                                if (
                                    $attributeModel->type == 'select' && $attributeModel->type_values != null
                                    && $attributeModel->type_values != ''
                                ) {
                                    $checkArray = array_map('trim', explode(',', $attributeModel->type_values));
                                    if (!in_array($attribute['value'], $checkArray)) {
                                        throw new \Exception('Invalid attribute value provided');
                                    }
                                }

                                if ($attributeModel->type == 'pattern') {
                                    // Assuming the uploaded file is in $attrData['value']
                                    // $path = $attrData['value']->store('products/images/patterns', 'public');
                                    $base64Image = $attribute['value'];

                                    // Extract the image data and extension
                                    list($type, $base64Image) = explode(';', $base64Image);
                                    list(, $base64Image) = explode(',', $base64Image);
                                    list(, $extension) = explode('/', $type);

                                    // Decode the base64 string
                                    $decodedImage = base64_decode($base64Image);

                                    // Check if the image was successfully decoded
                                    if ($decodedImage === false) {
                                        throw new \Exception('Unable to decode the base64 image');
                                    }

                                    // Generate a unique filename
                                    $filename = 'patterns_' . time() . '_' . uniqid() . '.' . $extension;
                                    $path = 'products/images/patterns/' . $filename;

                                    // Store the decoded image
                                    Storage::disk('public')->put($path, $decodedImage);
                                    $variant->variantValues()->create([
                                        'variant_attribute_id' => $attribute['attribute_id'],
                                        'value' => $path
                                    ]);
                                } else {
                                    $variant->variantValues()->create([
                                        'variant_attribute_id' => $attribute['attribute_id'],
                                        'value' => $attribute['value']
                                    ]);
                                }

                                // Here apply Changes  
                            }
                        }
                    }
                }
            }

            // Add new variants
            if ($request->has('new_variants')) {
                foreach ($request->new_variants as $newVariantData) {
                    // Generate SKU for new variant
                    $sku = $this->generateSku($product, $newVariantData);

                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'price' => $newVariantData['price'],
                        'cost' => $newVariantData['cost'],
                        'stock' => $newVariantData['stock'],
                        'sku' => $sku,
                        'expiry_date' => $newVariantData['expiry_date'] ?? null,
                        'expiry_days' => $newVariantData['expiry_days'] ?? null,
                        'is_active' => true,
                        'is_out_of_stock' => $newVariantData['stock'] <= 0
                    ]);

                    // Create attribute values for new variant
                    foreach ($newVariantData['attributes'] as $attribute) {
                        $attributeModel = VariantAttribute::find($attribute['attribute_id']);
                        if (!$attributeModel) {
                            throw new \Exception('Invalid attribute provided');
                        }

                        if (
                            $attributeModel->type == 'select' && $attributeModel->type_values != null
                            && $attributeModel->type_values != ''
                        ) {
                            $checkArray = array_map('trim', explode(',', $attributeModel->type_values));
                            if (!in_array($attribute['value'], $checkArray)) {
                                throw new \Exception('Invalid attribute value provided');
                            }
                        }

                        if ($attributeModel->type == 'pattern') {
                            // Assuming the uploaded file is in $attrData['value']
                            // $path = $attrData['value']->store('products/images/patterns', 'public');
                            $base64Image = $attribute['value'];

                            // Extract the image data and extension
                            list($type, $base64Image) = explode(';', $base64Image);
                            list(, $base64Image) = explode(',', $base64Image);
                            list(, $extension) = explode('/', $type);

                            // Decode the base64 string
                            $decodedImage = base64_decode($base64Image);

                            // Check if the image was successfully decoded
                            if ($decodedImage === false) {
                                throw new \Exception('Unable to decode the base64 image');
                            }

                            // Generate a unique filename
                            $filename = 'patterns_' . time() . '_' . uniqid() . '.' . $extension;
                            $path = 'products/images/patterns/' . $filename;

                            // Store the decoded image
                            Storage::disk('public')->put($path, $decodedImage);
                            $variant->variantValues()->create([
                                'variant_attribute_id' => $attribute['attribute_id'],
                                'value' => $path
                            ]);
                        } else {
                            $variant->variantValues()->create([
                                'variant_attribute_id' => $attribute['attribute_id'],
                                'value' => $attribute['value']
                            ]);
                        }
                    }
                }
            }

            // Delete variants if requested
            if ($request->has('delete_variants')) {
                ProductVariant::where('product_id', $product->id)
                    ->whereIn('id', $request->delete_variants)
                    ->delete();
            }

            DB::commit();

            // Fetch updated product with all relations
            $updatedProduct = Product::with([
                'variants' => function ($query) {
                    $query->with(['variantValues.variantAttribute']);
                },
                'images',
                'category'
            ])->find($product->id);

            return response()->json([
                'status' => true,
                'message' => 'Product updated successfully',
                'data' => $updatedProduct
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }



    public function deleteProductImages(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);

            if ($request->has('delete_images')) {
                foreach ($request->delete_images as $imageId) {
                    $image = ProductImage::find($imageId);
                    if ($image && $image->product_id === $product->id) {
                        if (Storage::disk('public')->exists($image->image)) {
                            Storage::disk('public')->delete($image->image);
                        }
                        $image->delete();
                    }
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Images deleted successfully',
                'data' => Product::with('images')->find($product->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }







    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $product = Product::with(['images', 'variants.variantValues'])->find($id);

            if (!$product) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            // Delete all product images from storage
            foreach ($product->images as $image) {
                if (Storage::disk('public')->exists($image->image)) {
                    Storage::disk('public')->delete($image->image);
                }
            }

            // Delete all variants and their values
            foreach ($product->variants as $variant) {
                // Delete variant values
                $variant->variantValues()->delete();
            }

            // Delete variants
            $product->variants()->delete();

            // Delete images records
            $product->images()->delete();

            // Finally, delete the product
            $product->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Product and all its associated data deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate SKU for a product variant
     */
    private function generateSku($product, $variantData)
    {
        // Get prefix from Arabic name (first 3 letters)
        $randomString = Str::random(3);
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $randomString), 0, 3));

        // Product ID padded to 4 digits
        $productId = str_pad($product->id, 4, '0', STR_PAD_LEFT);

        // Variant number (count of existing variants + 1)
        $variantCount = $product->variants()->count() + 1;
        $variantNumber = str_pad($variantCount, 2, '0', STR_PAD_LEFT);

        // Get attribute values sorted by attribute_id
        $attributeValues = collect($variantData['attributes'])
            ->sortBy('attribute_id')
            ->map(function ($attr) {
                return strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $attr['value']), 0, 2));
            })
            ->join('');
        return "{$prefix}-{$productId}-{$variantNumber}-{$attributeValues}";
    }






    /**
     * Search and filter products
     */
    public function search(Request $request)
    {
        try {
            $latestExchange = PriceExchange::latest('created_at')->first();
            $latestRate = $latestExchange->price_rate ?? 1;
            $globalMarkup = \Modules\Admin\Models\PlatformMarkup::getLatest();

            $query = Product::with(['images', 'category', 'vendor'])
                ->select('products.*')
                ->addSelect([
                    'min_price' => ProductVariant::selectRaw('price / ?', [$latestRate])
                        ->whereColumn('product_id', 'products.id')
                        ->where('is_active', true)
                        ->orderBy('price', 'asc')
                        ->limit(1),
                    'max_price' => ProductVariant::selectRaw('price / ?', [$latestRate])
                        ->whereColumn('product_id', 'products.id')
                        ->where('is_active', true)
                        ->orderBy('price', 'desc')
                        ->limit(1)
                ]);
            // Text Search (name and description)
            if ($request->has('search')) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name_arabic', 'like', "%{$searchTerm}%")
                        ->orWhere('name_german', 'like', "%{$searchTerm}%")
                        ->orWhere('description_arabic', 'like', "%{$searchTerm}%")
                        ->orWhere('description_german', 'like', "%{$searchTerm}%");
                });
            }

            // Category Filter — match either the top-level category or the
            // sub-category. Products keep their parent in category_id and their
            // subcategory in child_category_id, so selecting a subcategory must
            // look at child_category_id too (otherwise the result is empty).
            if ($request->has('category_id')) {
                $catId = $request->category_id;
                $query->where(function ($q) use ($catId) {
                    $q->where('category_id', $catId)
                        ->orWhere('child_category_id', $catId);
                });
            }

            if ($request->has('child_category_id')) {
                $query->where('child_category_id', $request->child_category_id);
            }

            // Price Range Filter
            if ($request->has('price_from') || $request->has('price_to')) {
                $query->whereHas('variants', function ($q) use ($request) {
                    if ($request->has('price_from')) {
                        $q->where('price', '>=', $request->price_from);
                    }
                    if ($request->has('price_to')) {
                        $q->where('price', '<=', $request->price_to);
                    }
                    $q->where('is_active', true);
                });
            }

            // Color Filter — colors live on the product images (texture-based
            // color system), so match products that have an image of that color.
            if ($request->has('color')) {
                $query->whereHas('images', function ($q) use ($request) {
                    $q->where('color', $request->color);
                });
            }

            // Size Filter
            if ($request->has('size')) {
                $query->whereHas('variants.variantValues', function ($q) use ($request) {
                    $q->whereHas('variantAttribute', function ($qa) {
                        $qa->where('name_arabic', 'المقاس')
                            ->orWhere('name_german', 'Größe');
                    })->where('value', $request->size);
                });
            }

            // Sort options
            $sortField = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');

            switch ($sortField) {
                case 'price_low':
                    $query->orderBy('min_price', 'asc');
                    break;
                case 'price_high':
                    $query->orderBy('max_price', 'desc');
                    break;
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'name_arabic':
                    $query->orderBy('name_arabic', $sortDirection);
                    break;
                case 'name_german':
                    $query->orderBy('name_german', $sortDirection);
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }

            // Get available filters
            $availableFilters = $this->getAvailableFilters();

            $products = $query->paginate($request->get('per_page', 15));

            // Apply markup to min_price and max_price
            $products->getCollection()->transform(function ($product) use ($globalMarkup) {
                $vendor = $product->vendor;
                $markup = ($vendor && $vendor->markup_percentage !== null)
                    ? (float) $vendor->markup_percentage
                    : (float) $globalMarkup;
                
                if ($product->min_price) {
                    $product->min_price = round($product->min_price * (1 + $markup / 100), 2);
                }
                if ($product->max_price) {
                    $product->max_price = round($product->max_price * (1 + $markup / 100), 2);
                }
                unset($product->vendor);
                return $product;
            });

            return response()->json([
                'status' => true,
                'data' => [
                    'products' => $products,
                    'filters' => $availableFilters
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available filters for products
     */
    private function getAvailableFilters()
    {
        try {
            // Only surface categories that actually have sellable stock, so the
            // storefront menu reflects what a visitor can really buy. A product
            // counts if it is active and has at least one active, in-stock variant.
            // It contributes both its parent (category_id) and its subcategory
            // (child_category_id) to the set of "non-empty" categories.
            $stockCategoryIds = Product::query()
                ->where('is_active', true)
                ->whereHas('variants', function ($q) {
                    $q->where('is_active', true)->where('is_out_of_stock', false);
                })
                ->get(['category_id', 'child_category_id'])
                ->flatMap(fn ($p) => [$p->category_id, $p->child_category_id])
                ->filter()
                ->unique()
                ->values();

            // Top-level categories that have stock, each with only its non-empty
            // subcategories attached.
            $categories = Category::select('id', 'name_arabic', 'name_german')
                ->whereNull('parent_id')
                ->whereIn('id', $stockCategoryIds)
                ->with(['subcategories' => function ($q) use ($stockCategoryIds) {
                    $q->select('id', 'parent_id', 'name_arabic', 'name_german')
                        ->whereIn('id', $stockCategoryIds);
                }])
                ->get();

            // Available colors come from the product images (texture-based color
            // system), matching how the storefront renders + filters colors.
            $colors = DB::table('products_images')
                ->whereNotNull('color')
                ->where('color', '!=', '')
                ->distinct()
                ->orderBy('color')
                ->pluck('color');

            // Get available sizes
            $sizes = DB::table('variant_values')
                ->join('variant_attributes', 'variant_values.variant_attribute_id', '=', 'variant_attributes.id')
                ->where(function ($q) {
                    $q->where('variant_attributes.name_arabic', 'المقاس')
                        ->orWhere('variant_attributes.name_german', 'Größe');
                })
                ->select('variant_values.value')
                ->distinct()
                ->pluck('value');

            // Get price range
            $priceRange = ProductVariant::select(
                DB::raw('MIN(price) as min_price'),
                DB::raw('MAX(price) as max_price')
            )->where('is_active', true)->first();

            return [
                'categories' => $categories,
                'colors' => $colors,
                'sizes' => $sizes,
                'price_range' => [
                    'min' => $priceRange->min_price ?? 0,
                    'max' => $priceRange->max_price ?? 0
                ],
                'sort_options' => [
                    ['key' => 'newest', 'name_arabic' => 'الأحدث', 'name_german' => 'Neueste'],
                    ['key' => 'price_low', 'name_arabic' => 'السعر: من الأقل إلى الأعلى', 'name_german' => 'Preis: Niedrig zu Hoch'],
                    ['key' => 'price_high', 'name_arabic' => 'السعر: من الأعلى إلى الأقل', 'name_german' => 'Preis: Hoch zu Niedrig'],
                    ['key' => 'name_arabic', 'name_arabic' => 'الاسم: أ-ي', 'name_german' => 'Name: A-Z (Arabisch)'],
                    ['key' => 'name_german', 'name_arabic' => 'الاسم: A-Z', 'name_german' => 'Name: A-Z (Deutsch)']
                ]
            ];
        } catch (\Exception $e) {
            return [];
        }
    }
}
