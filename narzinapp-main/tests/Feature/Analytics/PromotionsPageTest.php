<?php

namespace Tests\Feature\Analytics;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Admin\Models\UserAdmin;
use Tests\TestCase;

class PromotionsPageTest extends TestCase
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

    public function test_admin_sees_promotions_page(): void
    {
        $this->actingAs($this->admin())
            ->get(route('statistics.promotions'))
            ->assertOk()
            ->assertSee('Coupons')
            ->assertSee('Promotions');
    }

    public function test_guest_cannot_reach_promotions_page(): void
    {
        $this->get(route('statistics.promotions'))->assertRedirect();
    }
}
