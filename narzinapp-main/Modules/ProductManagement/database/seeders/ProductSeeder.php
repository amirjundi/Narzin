<?php

namespace Modules\ProductManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Admin\Models\ColorTag;
use Modules\ProductManagement\Models\Category;
use Modules\ProductManagement\Models\Product;
use Modules\ProductManagement\Models\ProductImage;
use Modules\ProductManagement\Models\ProductVariant;
use Modules\ProductManagement\Models\VariantAttribute;
use Modules\ProductManagement\Models\VariantValue;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ProductSeeder extends Seeder
{
    protected $imageUrls = [
        'https://images.unsplash.com/photo-1523275335684-37898b6baf30',
        'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f',
        'https://images.unsplash.com/photo-1505740420928-5e560c06d30e',
        'https://images.unsplash.com/photo-1542291026-7eec264c27ff',
        'https://images.unsplash.com/photo-1581235720704-06d3acfcb36f',
        'https://images.unsplash.com/photo-1546868871-7041f2a55e12'
    ];

    private function downloadAndSaveImage($url, $product_id)
    {
        try {
            $client = new Client();
            $response = $client->get($url);
            
            $filename = 'product_' . $product_id . '_' . Str::random(10) . '.jpg';
            $path = 'products/images/' . $filename;
            
            Storage::disk('public')->put($path, $response->getBody());
            
            return $path;
        } catch (\Exception $e) {
            Log::error('Failed to download image: ' . $e->getMessage());
            return null;
        }
    }

    private function simpleSlug($text) 
    {
        $text = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        return strtolower(trim($text, '-'));
    }

    public function run(): void
    {
        Log::info('Starting product seeding...');
        
        $categories = Category::all();
        $colorTags = ColorTag::all();
        $variantAttributes = VariantAttribute::all();
        
        $colorAttribute = $variantAttributes->where('type', 'color')->first();
        $sizeAttribute = $variantAttributes->where('type', 'select')->first();
        
        $sizeValues = array_map('trim', explode(',', $sizeAttribute->type_values));

        foreach ($categories as $category) {
            Log::info("Processing category: {$category->name_german}");
            
            for ($i = 1; $i <= 5; $i++) {
                $productName = "Produkt {$i} in {$category->name_german}";
                $productNameAr = "منتج{$i} في {$category->name_arabic}";
                
                $product = Product::create([
                    'name_arabic' => $productNameAr,
                    'name_german' => $productName,
                    'slug_arabic' => $this->simpleSlug($productNameAr),
                    'slug_german' => $this->simpleSlug($productName),
                    'description_arabic' => "وصف المنتج{$i} في {$category->name_arabic}",
                    'description_german' => "Beschreibung für Produkt {$i} in {$category->name_german}",
                    'category_id' => $category->id,
                    'child_category_id' => Category::where('parent_id', $category->id)->inRandomOrder()->first()->id ?? null,
                    'vendor_id' => DB::table('vendors')->inRandomOrder()->first()->id,
                    'is_active' => true,
                ]);

                // Download images
                $shuffledUrls = collect($this->imageUrls)->shuffle()->take(4);
                foreach ($shuffledUrls as $url) {
                    $imagePath = $this->downloadAndSaveImage($url, $product->id);
                    if ($imagePath) {
                        ProductImage::create([
                            'product_id' => $product->id,
                            'image' => $imagePath
                        ]);
                    }
                }

                // Create variants
                for ($j = 1; $j <= 3; $j++) {
                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'price' => rand(100, 1000),
                        'stock' => rand(0, 100),
                        'sku' => "SKU-{$product->id}-{$j}",
                        'is_active' => true,
                        'is_out_of_stock' => false,
                        'color_tag_id' => $colorTags->random()->id,
                    ]);

                    // Add color variant
                    VariantValue::create([
                        'product_variants_id' => $variant->id,
                        'variant_attribute_id' => $colorAttribute->id,
                        'value' => '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT),
                    ]);

                    // Add size variant
                    VariantValue::create([
                        'product_variants_id' => $variant->id,
                        'variant_attribute_id' => $sizeAttribute->id,
                        'value' => $sizeValues[array_rand($sizeValues)],
                    ]);
                }
            }
        }

        Log::info('Product seeding completed!');
    }
}