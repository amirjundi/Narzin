<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Modules\HomeContent\Models\HomeBlock;
use Modules\HomeContent\Services\HomeFeedService;
use Modules\ProductManagement\Models\Category;
use Tests\TestCase;

class HomeFeedServiceTest extends TestCase
{
    use RefreshDatabase;

    private HomeFeedService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(HomeFeedService::class);
        config(['app.url' => 'https://api.test']);
    }

    private function announcement(array $overrides = []): HomeBlock
    {
        return HomeBlock::create(array_merge([
            'type' => 'announcement_bar', 'name' => 'Bar', 'platform' => 'both',
            'is_active' => true, 'sort_order' => 1,
            'content' => ['text' => ['ar' => 'حمل التطبيق', 'de' => 'Hol dir die App']],
        ], $overrides));
    }

    public function test_feed_resolves_locale_with_fallback(): void
    {
        $this->announcement();

        $de = $this->service->feed('web', 'de');
        $en = $this->service->feed('web', 'en');

        $this->assertSame('Hol dir die App', $de[0]['content']['text']);
        $this->assertSame('حمل التطبيق', $en[0]['content']['text']); // en missing → ar fallback
        $this->assertSame('#141923', $de[0]['content']['bg_color']);
    }

    public function test_hero_slider_picks_platform_image_and_absolute_url(): void
    {
        HomeBlock::create([
            'type' => 'hero_slider', 'name' => 'Hero', 'platform' => 'both',
            'is_active' => true, 'sort_order' => 2,
            'content' => ['slides' => [
                ['image_web' => 'homeBlocks/web.jpg', 'image_app' => 'homeBlocks/app.jpg', 'title' => ['ar' => 'صيف']],
                ['image_web' => null, 'image_app' => null], // unrenderable → dropped
            ]],
        ]);

        $web = $this->service->feed('web', 'ar');
        Cache::flush();
        $app = $this->service->feed('app', 'ar');

        $this->assertCount(1, $web[0]['content']['slides']);
        $this->assertSame('https://api.test/storage/homeBlocks/web.jpg', $web[0]['content']['slides'][0]['image']);
        $this->assertSame('https://api.test/storage/homeBlocks/app.jpg', $app[0]['content']['slides'][0]['image']);
    }

    public function test_empty_and_expired_blocks_are_dropped(): void
    {
        HomeBlock::create([
            'type' => 'product_rail', 'name' => 'Empty rail', 'platform' => 'both',
            'is_active' => true, 'sort_order' => 1,
            'content' => ['title' => ['en' => 'Deals'], 'rule' => 'manual', 'product_ids' => [999999]],
        ]);
        HomeBlock::create([
            'type' => 'countdown_banner', 'name' => 'Over', 'platform' => 'both',
            'is_active' => true, 'sort_order' => 2,
            'content' => ['text' => ['en' => 'Sale'], 'ends_at_display' => now()->subHour()->toDateTimeString()],
        ]);
        HomeBlock::create([
            'type' => 'category_grid', 'name' => 'Ghost cats', 'platform' => 'both',
            'is_active' => true, 'sort_order' => 3, 'content' => ['category_ids' => [999999]],
        ]);

        $this->assertSame([], $this->service->feed('web', 'en'));
    }

    public function test_malformed_content_is_skipped_not_fatal(): void
    {
        HomeBlock::create([
            'type' => 'hero_slider', 'name' => 'Broken', 'platform' => 'both',
            'is_active' => true, 'sort_order' => 1, 'content' => ['slides' => 'not-an-array'],
        ]);
        $this->announcement(['sort_order' => 2]);

        $feed = $this->service->feed('web', 'ar');

        $this->assertCount(1, $feed);
        $this->assertSame('announcement_bar', $feed[0]['type']);
    }

    public function test_category_grid_resolves_names_and_order(): void
    {
        $a = Category::create(['name_arabic' => 'أ', 'name_german' => 'A', 'slug_arabic' => 'a-' . uniqid(), 'slug_german' => 'a2-' . uniqid()]);
        $b = Category::create(['name_arabic' => 'ب', 'name_german' => 'B', 'slug_arabic' => 'b-' . uniqid(), 'slug_german' => 'b2-' . uniqid()]);
        HomeBlock::create([
            'type' => 'category_grid', 'name' => 'Cats', 'platform' => 'both',
            'is_active' => true, 'sort_order' => 1, 'content' => ['category_ids' => [$b->id, $a->id]],
        ]);

        $feed = $this->service->feed('web', 'de');

        $this->assertSame(['B', 'A'], array_column($feed[0]['content']['categories'], 'name'));
    }

    public function test_feed_is_cached_and_flushed_on_save(): void
    {
        $block = $this->announcement();
        $this->assertCount(1, $this->service->feed('web', 'ar'));

        $block->update(['is_active' => false]); // model event must flush the cache

        $this->assertSame([], $this->service->feed('web', 'ar'));
    }

    public function test_preview_includes_upcoming_blocks_flagged(): void
    {
        $this->announcement(['name' => 'live']);
        $this->announcement(['name' => 'soon', 'sort_order' => 2, 'starts_at' => now()->addDay()]);

        $normal = $this->service->feed('web', 'ar');
        $preview = $this->service->feed('web', 'ar', true);

        $this->assertCount(1, $normal);
        $this->assertCount(2, $preview);
        $this->assertTrue($preview[1]['preview_upcoming']);
    }
}
