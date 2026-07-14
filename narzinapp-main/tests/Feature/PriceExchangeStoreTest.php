<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Admin\Models\PriceExchange;
use Modules\Admin\Models\UserAdmin;
use Tests\TestCase;

class PriceExchangeStoreTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): self
    {
        $admin = User::create([
            'name' => 'Admin', 'email' => 'admin' . uniqid() . '@t.test',
            'password' => 'secret123', 'email_verified_at' => now(),
        ]);
        UserAdmin::create(['user_id' => $admin->id, 'is_active' => 1]);
        $this->actingAs($admin);

        return $this;
    }

    /**
     * Regression: storing a rate used to 500 — the controller called the
     * undefined `auth('admin')` guard and set a `created_by` column that does
     * not exist on price_exechange. Both are gone; the rate should just save.
     */
    public function test_storing_an_exchange_rate_saves_and_redirects(): void
    {
        $this->actingAsAdmin()
            ->post(route('price-exchange.store'), ['price_rate' => 500])
            ->assertRedirect(route('price-exchange.index'));

        $this->assertDatabaseCount('price_exechange', 1);
        $this->assertEqualsWithDelta(500.0, (float) PriceExchange::first()->price_rate, 0.001);
    }

    public function test_rate_below_minimum_is_rejected(): void
    {
        $this->actingAsAdmin()
            ->post(route('price-exchange.store'), ['price_rate' => 0])
            ->assertSessionHasErrors('price_rate');

        $this->assertDatabaseCount('price_exechange', 0);
    }
}
