<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Admin\Models\UserAdmin;
use Modules\Checkout\Models\Promotion;
use Tests\TestCase;

class PromotionAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $a = User::create(['name' => 'A', 'email' => 'a' . uniqid() . '@t.test', 'password' => 'x', 'email_verified_at' => now()]);
        UserAdmin::create(['user_id' => $a->id, 'is_active' => 1]);
        return $a;
    }

    public function test_admin_can_create_a_percentage_promotion(): void
    {
        $this->actingAs($this->admin())
            ->post(route('promotions.store'), [
                'name' => '10% over 75', 'type' => 'percentage', 'value' => 10,
                'minimum_cart_amount' => 75, 'absorbed_by_vendor_percentage' => 40, 'is_active' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('promotions', ['name' => '10% over 75', 'type' => 'percentage', 'value' => 10]);
    }

    public function test_free_shipping_promotion_does_not_require_value(): void
    {
        $this->actingAs($this->admin())
            ->post(route('promotions.store'), [
                'name' => 'Free ship 100', 'type' => 'free_shipping',
                'minimum_cart_amount' => 100, 'is_active' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('promotions', ['name' => 'Free ship 100', 'type' => 'free_shipping']);
    }

    public function test_percentage_promotion_requires_value(): void
    {
        $this->actingAs($this->admin())
            ->post(route('promotions.store'), [
                'name' => 'Bad', 'type' => 'percentage', 'minimum_cart_amount' => 50, 'is_active' => 1,
            ])
            ->assertSessionHasErrors('value');
    }
}
