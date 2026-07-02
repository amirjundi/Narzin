<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HomeContent\Models\HomeBlock;
use Tests\TestCase;

class HomeFeedApiTest extends TestCase
{
    use RefreshDatabase;

    private function announcement(array $overrides = []): HomeBlock
    {
        return HomeBlock::create(array_merge([
            'type' => 'announcement_bar', 'name' => 'Bar', 'platform' => 'both',
            'is_active' => true, 'sort_order' => 1,
            'content' => ['text' => ['ar' => 'أهلا', 'de' => 'Hallo']],
        ], $overrides));
    }

    public function test_returns_resolved_feed_for_platform_and_locale(): void
    {
        $this->announcement();
        $this->announcement(['name' => 'app only', 'platform' => 'app', 'sort_order' => 2]);

        $response = $this->getJson('/api/v1/home?platform=web&locale=de');

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', 'announcement_bar')
            ->assertJsonPath('data.0.content.text', 'Hallo');
    }

    public function test_du_locale_is_normalized_to_de(): void
    {
        $this->announcement();

        $this->getJson('/api/v1/home?platform=web&locale=du')
            ->assertOk()
            ->assertJsonPath('data.0.content.text', 'Hallo');
    }

    public function test_platform_defaults_to_web_and_invalid_platform_is_422(): void
    {
        $this->announcement(['platform' => 'web']);

        $this->getJson('/api/v1/home')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/v1/home?platform=tv')->assertStatus(422);
    }

    public function test_preview_requires_matching_token(): void
    {
        config(['homecontent.preview_token' => 'secret-token']);
        $this->announcement(['starts_at' => now()->addDay()]);

        $this->getJson('/api/v1/home?preview=1')->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/home?preview=1&preview_token=wrong')->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/home?preview=1&preview_token=secret-token')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.preview_upcoming', true);
    }
}
