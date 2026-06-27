<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Models\UserAdmin;
use Modules\ProductManagement\Models\Product;
use Modules\ProductManagement\Models\ProductImage;
use Modules\ProductManagement\Models\ProductVariant;
use Tests\TestCase;

class AdminProductUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::create([
            'name' => 'Admin', 'email' => 'a' . uniqid() . '@t.test',
            'password' => 'secret123', 'email_verified_at' => now(),
        ]);
        UserAdmin::create(['user_id' => $admin->id, 'is_active' => 1]);
        return $admin;
    }

    private function categoryId(): int
    {
        return DB::table('categories')->insertGetId([
            'name_arabic' => 'ف', 'name_german' => 'K',
            'slug_arabic' => 'c' . uniqid(), 'slug_german' => 'c' . uniqid(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_update_edits_existing_variant_and_deletes_image(): void
    {
        $product = Product::create([
            'name_arabic' => 'منتج', 'name_german' => 'Produkt',
            'slug_arabic' => 'p' . uniqid(), 'slug_german' => 'p' . uniqid(),
            'category_id' => $this->categoryId(), 'is_active' => true,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id, 'price' => 10, 'cost' => 0, 'stock' => 5,
            'tax' => 0, 'sku' => 'SKU-' . uniqid(), 'is_active' => true, 'is_out_of_stock' => false,
        ]);
        $image = ProductImage::create(['product_id' => $product->id, 'image' => 'products/images/x.jpg', 'color' => null]);

        $this->actingAs($this->admin())
            ->put(route('products.update', $product->id), [
                'name_arabic' => $product->name_arabic,
                'name_german' => $product->name_german,
                'existing_variants' => [$variant->id => ['price' => 999, 'stock' => 0]],
                'delete_images' => json_encode([$image->id]),
            ])
            ->assertJson(['success' => true]);

        $fresh = $variant->fresh();
        $this->assertEquals(999, $fresh->price);
        $this->assertEquals(0, $fresh->stock);
        $this->assertTrue((bool) $fresh->is_out_of_stock);          // stock 0 -> out of stock
        $this->assertNull(ProductImage::withoutGlobalScope('image_url')->find($image->id)); // image deleted
    }

    public function test_update_deletes_a_variant(): void
    {
        $product = Product::create([
            'name_arabic' => 'منتج', 'name_german' => 'Produkt',
            'slug_arabic' => 'p' . uniqid(), 'slug_german' => 'p' . uniqid(),
            'category_id' => $this->categoryId(), 'is_active' => true,
        ]);
        $keep = ProductVariant::create([
            'product_id' => $product->id, 'price' => 10, 'cost' => 0, 'stock' => 5,
            'tax' => 0, 'sku' => 'K-' . uniqid(), 'is_active' => true, 'is_out_of_stock' => false,
        ]);
        $drop = ProductVariant::create([
            'product_id' => $product->id, 'price' => 20, 'cost' => 0, 'stock' => 5,
            'tax' => 0, 'sku' => 'D-' . uniqid(), 'is_active' => true, 'is_out_of_stock' => false,
        ]);

        $this->actingAs($this->admin())
            ->put(route('products.update', $product->id), [
                'name_arabic' => $product->name_arabic,
                'name_german' => $product->name_german,
                'delete_variants' => json_encode([$drop->id]),
            ])
            ->assertJson(['success' => true]);

        $this->assertNotNull(ProductVariant::find($keep->id));
        $this->assertNull(ProductVariant::find($drop->id));
    }
}
