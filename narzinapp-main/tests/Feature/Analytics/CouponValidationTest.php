<?php

namespace Tests\Feature\Analytics;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Admin\Models\UserAdmin;
use Modules\Checkout\Models\Coupon;
use Tests\TestCase;

/**
 * Coupon discount_amount must be non-negative (and <=100 for a percentage) —
 * a negative coupon corrupts checkout math (price_after_discount > total_amount)
 * and shows a negative "discount given" on the Promotions report.
 */
class CouponValidationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::create([
            'name' => 'Admin', 'email' => 'admin' . uniqid() . '@t.test',
            'password' => 'x', 'email_verified_at' => now(),
        ]);
        UserAdmin::create(['user_id' => $user->id, 'is_active' => 1]);
        return $user;
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'code' => 'C' . uniqid(),
            'discount_amount' => 10,
            'discount_type' => 'fixed',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addWeek()->toDateString(),
        ], $overrides);
    }

    public function test_negative_discount_amount_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->post(route('coupons.store'), $this->payload(['discount_amount' => -5]))
            ->assertSessionHasErrors('discount_amount');

        $this->assertDatabaseCount('coupons', 0);
    }

    public function test_percentage_over_100_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->post(route('coupons.store'), $this->payload(['discount_type' => 'percentage', 'discount_amount' => 150]))
            ->assertSessionHasErrors('discount_amount');

        $this->assertDatabaseCount('coupons', 0);
    }

    public function test_valid_coupon_is_accepted(): void
    {
        $this->actingAs($this->admin())
            ->post(route('coupons.store'), $this->payload(['discount_amount' => 15]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('coupons', 1);
    }
}
