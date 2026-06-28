<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Checkout\Models\Promotion;
use Tests\TestCase;

class PromotionModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_scope_returns_only_active_in_window_promotions(): void
    {
        Promotion::create(['name' => 'Live', 'type' => 'percentage', 'value' => 10, 'minimum_cart_amount' => 50, 'is_active' => true]);
        Promotion::create(['name' => 'Disabled', 'type' => 'percentage', 'value' => 10, 'minimum_cart_amount' => 50, 'is_active' => false]);
        Promotion::create(['name' => 'Expired', 'type' => 'fixed', 'value' => 5, 'minimum_cart_amount' => 50, 'is_active' => true, 'end_date' => now()->subDay()->toDateString()]);
        Promotion::create(['name' => 'Future', 'type' => 'fixed', 'value' => 5, 'minimum_cart_amount' => 50, 'is_active' => true, 'start_date' => now()->addDay()->toDateString()]);

        $names = Promotion::active()->pluck('name')->all();

        $this->assertSame(['Live'], $names);
    }
}
