<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HomeContent\Models\HomeBlock;
use Tests\TestCase;

class LegacyBannerEndpointsTest extends TestCase
{
    use RefreshDatabase;

    private function hero(): HomeBlock
    {
        return HomeBlock::create([
            'type' => 'hero_slider', 'name' => 'Hero', 'platform' => 'both',
            'is_active' => true, 'sort_order' => 1,
            'content' => ['slides' => [[
                'image_web' => 'homeBlocks/w.jpg', 'image_app' => 'homeBlocks/a.jpg',
                'title' => ['ar' => 'تخفيضات'], 'subtitle' => ['ar' => 'حتى ٥٠٪'],
            ]]],
        ]);
    }

    public function test_legacy_mobile_banners_shape(): void
    {
        config(['app.url' => 'https://api.test']);
        $this->hero();

        $this->getJson('/api/v1/banners/mobile')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.0.image', 'https://api.test/storage/homeBlocks/a.jpg')
            ->assertJsonPath('data.0.title', 'تخفيضات')
            ->assertJsonPath('data.0.description', 'حتى ٥٠٪')
            ->assertJsonPath('data.0.is_mobile', 1);
    }

    public function test_legacy_web_banners_use_web_image(): void
    {
        config(['app.url' => 'https://api.test']);
        $this->hero();

        $this->getJson('/api/v1/banners/web')
            ->assertOk()
            ->assertJsonPath('data.0.image', 'https://api.test/storage/homeBlocks/w.jpg')
            ->assertJsonPath('data.0.is_mobile', 0);
    }

    public function test_legacy_before_nav_returns_current_announcement(): void
    {
        HomeBlock::create([
            'type' => 'announcement_bar', 'name' => 'Bar', 'platform' => 'both',
            'is_active' => true, 'sort_order' => 0,
            'content' => ['text' => ['ar' => 'شحن مجاني']],
        ]);

        $this->getJson('/api/v1/before-nav/current')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.text', 'شحن مجاني');
    }

    public function test_legacy_before_nav_404_when_none(): void
    {
        $this->getJson('/api/v1/before-nav/current')
            ->assertStatus(404)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', null);
    }

    public function test_platform_split_is_preserved_for_single_image_slides(): void
    {
        config(['app.url' => 'https://api.test']);

        HomeBlock::create([
            'type' => 'hero_slider', 'name' => 'Web hero', 'platform' => 'web',
            'is_active' => true, 'sort_order' => 1,
            'content' => ['slides' => [[
                'image_web' => 'homeBlocks/w-only.jpg', 'image_app' => null,
            ]]],
        ]);

        HomeBlock::create([
            'type' => 'hero_slider', 'name' => 'App hero', 'platform' => 'app',
            'is_active' => true, 'sort_order' => 2,
            'content' => ['slides' => [[
                'image_web' => null, 'image_app' => 'homeBlocks/a-only.jpg',
            ]]],
        ]);

        $this->getJson('/api/v1/banners/web')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.image', 'https://api.test/storage/homeBlocks/w-only.jpg');

        $this->getJson('/api/v1/banners/mobile')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.image', 'https://api.test/storage/homeBlocks/a-only.jpg');
    }
}
