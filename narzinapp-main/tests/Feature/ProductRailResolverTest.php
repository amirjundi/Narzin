<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\HomeContent\Services\ProductRailResolver;
use Modules\ProductManagement\Models\Category;
use Modules\ProductManagement\Models\Product;
use Modules\ProductManagement\Models\ProductVariant;
use Tests\TestCase;

class ProductRailResolverTest extends TestCase
{
    use RefreshDatabase;

    private ProductRailResolver $resolver;
    private int $categoryId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new ProductRailResolver();
        $this->categoryId = Category::create([
            'name_arabic' => 'فساتين', 'name_german' => 'Kleider',
            'slug_arabic' => 'cat-ar-' . uniqid(), 'slug_german' => 'cat-de-' . uniqid(),
        ])->id;
    }

    private function product(string $name, float $price, array $overrides = []): Product
    {
        $product = Product::create(array_merge([
            'name_arabic' => $name, 'name_german' => $name,
            'slug_arabic' => 'p-ar-' . uniqid(), 'slug_german' => 'p-de-' . uniqid(),
            'category_id' => $this->categoryId, 'is_active' => true,
        ], $overrides));
        ProductVariant::create([
            'product_id' => $product->id, 'price' => $price, 'stock' => 10,
            'sku' => 'sku-' . uniqid(), 'is_active' => true, 'is_out_of_stock' => false,
        ]);

        return $product;
    }

    public function test_newest_returns_latest_first_with_prices(): void
    {
        $old = $this->product('old', 100);
        $old->created_at = now()->subDays(2);
        $old->save();
        $new = $this->product('new', 200);

        $cards = $this->resolver->resolve(['rule' => 'newest', 'limit' => 12]);

        $this->assertSame([$new->id, $old->id], array_column($cards, 'id'));
        $this->assertEquals(200.0, $cards[0]['min_price']);
        $this->assertEquals(200, $cards[0]['min_price_iqd']);
    }

    public function test_products_without_purchasable_variant_are_excluded(): void
    {
        $this->product('sellable', 50);
        $ghost = Product::create([
            'name_arabic' => 'ghost', 'name_german' => 'ghost',
            'slug_arabic' => 'g-ar-' . uniqid(), 'slug_german' => 'g-de-' . uniqid(),
            'category_id' => $this->categoryId, 'is_active' => true,
        ]);

        $cards = $this->resolver->resolve(['rule' => 'newest']);

        $this->assertNotContains($ghost->id, array_column($cards, 'id'));
        $this->assertCount(1, $cards);
    }

    public function test_inactive_products_are_excluded_and_limit_applies(): void
    {
        $this->product('hidden', 10, ['is_active' => false]);
        foreach (range(1, 4) as $i) {
            $this->product("p$i", 10 * $i);
        }

        $cards = $this->resolver->resolve(['rule' => 'newest', 'limit' => 3]);

        $this->assertCount(3, $cards);
        $this->assertNotContains('hidden', array_column($cards, 'name_german'));
    }

    public function test_manual_preserves_admin_order(): void
    {
        $a = $this->product('a', 10);
        $b = $this->product('b', 20);
        $c = $this->product('c', 30);

        $cards = $this->resolver->resolve(['rule' => 'manual', 'product_ids' => [$c->id, $a->id, $b->id]]);

        $this->assertSame([$c->id, $a->id, $b->id], array_column($cards, 'id'));
    }

    public function test_manual_truncates_in_admin_order(): void
    {
        $a = $this->product('a', 10);
        $b = $this->product('b', 20);
        $c = $this->product('c', 30);
        $d = $this->product('d', 40);

        $cards = $this->resolver->resolve([
            'rule' => 'manual',
            'product_ids' => [$c->id, $a->id, $d->id, $b->id],
            'limit' => 2,
        ]);

        $this->assertSame([$c->id, $a->id], array_column($cards, 'id'));
    }

    public function test_rail_backfills_when_invalid_product_is_newer(): void
    {
        $p1 = $this->product('p1', 10);
        $p1->created_at = now()->subDays(4);
        $p1->save();
        $p2 = $this->product('p2', 20);
        $p2->created_at = now()->subDays(3);
        $p2->save();
        $p3 = $this->product('p3', 30);
        $p3->created_at = now()->subDays(2);
        $p3->save();
        $ghost = Product::create([
            'name_arabic' => 'ghost', 'name_german' => 'ghost',
            'slug_arabic' => 'g-ar-' . uniqid(), 'slug_german' => 'g-de-' . uniqid(),
            'category_id' => $this->categoryId, 'is_active' => true,
        ]);
        $ghost->created_at = now();
        $ghost->save();

        $cards = $this->resolver->resolve(['rule' => 'newest', 'limit' => 3]);

        $this->assertCount(3, $cards);
        $this->assertNotContains($ghost->id, array_column($cards, 'id'));
    }

    public function test_category_rule_matches_category_and_child(): void
    {
        $inCat = $this->product('in', 10);
        $otherCat = Category::create([
            'name_arabic' => 'أخرى', 'name_german' => 'Andere',
            'slug_arabic' => 'o-ar-' . uniqid(), 'slug_german' => 'o-de-' . uniqid(),
        ]);
        $this->product('out', 20, ['category_id' => $otherCat->id]);

        $cards = $this->resolver->resolve(['rule' => 'category', 'category_id' => $this->categoryId]);

        $this->assertSame([$inCat->id], array_column($cards, 'id'));
    }

    public function test_best_sellers_orders_by_units_sold(): void
    {
        $slow = $this->product('slow', 10);
        $hit = $this->product('hit', 20);

        DB::statement('PRAGMA defer_foreign_keys = ON');
        DB::table('orders')->insert([
            'id' => 1, 'user_id' => 1, 'address_id' => 1, 'order_number' => 'T-1',
            'total_amount' => 100, 'created_at' => now(), 'updated_at' => now(),
        ]);
        foreach ([[$slow->id, 1], [$hit->id, 9]] as [$productId, $qty]) {
            DB::table('order_items')->insert([
                'order_id' => 1, 'product_id' => $productId, 'product_variant_id' => 1,
                'vendor_id' => 1, 'quantity' => $qty, 'unit_price' => 10, 'subtotal' => 10 * $qty,
                'final_price' => 10 * $qty, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        $cards = $this->resolver->resolve(['rule' => 'best_sellers']);

        $this->assertSame([$hit->id, $slow->id], array_column($cards, 'id'));
    }
}
