<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Validator;
use Modules\HomeContent\Support\BlockContentRules;
use Tests\TestCase;

class BlockContentRulesTest extends TestCase
{
    private function passes(string $type, array $content): bool
    {
        return !Validator::make(['content' => $content], BlockContentRules::for($type))->fails();
    }

    public function test_announcement_bar_requires_at_least_one_language(): void
    {
        $this->assertFalse($this->passes('announcement_bar', ['text' => ['ar' => '', 'de' => '', 'en' => '']]));
        $this->assertTrue($this->passes('announcement_bar', ['text' => ['ar' => 'مرحبا']]));
    }

    public function test_announcement_bar_validates_colors(): void
    {
        $this->assertFalse($this->passes('announcement_bar', ['text' => ['ar' => 'x'], 'bg_color' => 'red']));
        $this->assertTrue($this->passes('announcement_bar', ['text' => ['ar' => 'x'], 'bg_color' => '#141923']));
    }

    public function test_popup_requires_title_and_valid_frequency(): void
    {
        $this->assertFalse($this->passes('popup', ['frequency' => ['mode' => 'once_per_session']]));
        $this->assertFalse($this->passes('popup', ['title' => ['en' => 'Get the app'], 'frequency' => ['mode' => 'hourly']]));
        $this->assertTrue($this->passes('popup', ['title' => ['en' => 'Get the app'], 'frequency' => ['mode' => 'once_per_days', 'days' => 7], 'delay_seconds' => 3]));
    }

    public function test_hero_slider_requires_slides(): void
    {
        $this->assertFalse($this->passes('hero_slider', ['slides' => []]));
        $this->assertTrue($this->passes('hero_slider', ['slides' => [['title' => ['ar' => 'صيف']]]]));
    }

    public function test_product_rail_rules(): void
    {
        $this->assertFalse($this->passes('product_rail', ['title' => ['en' => 'Deals'], 'rule' => 'biggest_discount']));
        $this->assertFalse($this->passes('product_rail', ['title' => ['en' => 'Deals'], 'rule' => 'manual', 'product_ids' => []]));
        $this->assertTrue($this->passes('product_rail', ['title' => ['en' => 'New In'], 'rule' => 'newest', 'limit' => 12]));
    }

    public function test_info_strip_requires_two_to_four_items(): void
    {
        $this->assertFalse($this->passes('info_strip', ['items' => [['icon' => 'truck', 'text' => ['en' => 'Free shipping']]]]));
        $this->assertFalse($this->passes('info_strip', ['items' => [
            ['icon' => 'rocket', 'text' => ['en' => 'a']],
            ['icon' => 'truck', 'text' => ['en' => 'b']],
        ]]));
        $this->assertTrue($this->passes('info_strip', ['items' => [
            ['icon' => 'truck', 'text' => ['en' => 'Free shipping']],
            ['icon' => 'shield', 'text' => ['en' => 'Secure pay']],
        ]]));
    }

    public function test_category_grid_and_promo_tiles_and_countdown(): void
    {
        $this->assertFalse($this->passes('category_grid', ['category_ids' => [1]]));
        $this->assertFalse($this->passes('promo_tiles', ['tiles' => []]));
        $this->assertFalse($this->passes('countdown_banner', ['text' => ['en' => 'Sale'], 'ends_at_display' => '2001-01-01 00:00:00']));
        $this->assertTrue($this->passes('countdown_banner', ['text' => ['en' => 'Sale'], 'ends_at_display' => now()->addDay()->toDateTimeString()]));
    }
}
