<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HomeContent\Models\HomeBlock;
use Tests\TestCase;

class HomeBlockModelTest extends TestCase
{
    use RefreshDatabase;

    private function block(array $overrides = []): HomeBlock
    {
        return HomeBlock::create(array_merge([
            'type' => 'announcement_bar',
            'name' => 'Test block',
            'sort_order' => 0,
            'is_active' => true,
            'platform' => 'both',
            'content' => ['text' => ['ar' => 'مرحبا']],
        ], $overrides));
    }

    public function test_content_is_cast_to_array(): void
    {
        $block = $this->block();
        $this->assertSame('مرحبا', HomeBlock::find($block->id)->content['text']['ar']);
    }

    public function test_visible_filters_inactive_blocks(): void
    {
        $this->block(['name' => 'on']);
        $this->block(['name' => 'off', 'is_active' => false]);

        $this->assertSame(['on'], HomeBlock::visible('web')->pluck('name')->all());
    }

    public function test_visible_filters_by_platform(): void
    {
        $this->block(['name' => 'both', 'platform' => 'both']);
        $this->block(['name' => 'web-only', 'platform' => 'web']);
        $this->block(['name' => 'app-only', 'platform' => 'app']);

        $this->assertSame(['both', 'app-only'], HomeBlock::visible('app')->pluck('name')->all());
    }

    public function test_visible_respects_schedule_window(): void
    {
        $this->block(['name' => 'current', 'starts_at' => now()->subDay(), 'ends_at' => now()->addDay()]);
        $this->block(['name' => 'future', 'starts_at' => now()->addDay()]);
        $this->block(['name' => 'past', 'ends_at' => now()->subDay()]);
        $this->block(['name' => 'open-ended']);

        $this->assertSame(['current', 'open-ended'], HomeBlock::visible('web')->pluck('name')->all());
    }

    public function test_visible_orders_by_sort_order(): void
    {
        $this->block(['name' => 'second', 'sort_order' => 5]);
        $this->block(['name' => 'first', 'sort_order' => 1]);

        $this->assertSame(['first', 'second'], HomeBlock::visible('web')->pluck('name')->all());
    }
}
