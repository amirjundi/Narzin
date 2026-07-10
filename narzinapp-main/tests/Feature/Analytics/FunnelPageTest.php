<?php

namespace Tests\Feature\Analytics;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Admin\Models\UserAdmin;
use Tests\TestCase;

class FunnelPageTest extends TestCase
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

    public function test_admin_sees_funnel_page_with_stage_labels(): void
    {
        // The page renders all five stages regardless of data, so no seeding is
        // needed to assert the stage labels are present.
        $this->actingAs($this->admin())
            ->get(route('statistics.funnel'))
            ->assertOk()
            ->assertSee('Product View')
            ->assertSee('Order Placed');
    }

    public function test_abandoned_cart_empty_state_is_shown_when_none(): void
    {
        $this->actingAs($this->admin())
            ->get(route('statistics.funnel'))
            ->assertOk()
            ->assertSee('No abandoned carts');
    }

    public function test_guest_cannot_reach_funnel_page(): void
    {
        $this->get(route('statistics.funnel'))->assertRedirect();
    }
}
