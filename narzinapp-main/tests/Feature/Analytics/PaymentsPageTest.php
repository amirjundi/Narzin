<?php

namespace Tests\Feature\Analytics;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Admin\Models\UserAdmin;
use Tests\TestCase;

class PaymentsPageTest extends TestCase
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

    public function test_admin_sees_payments_page(): void
    {
        $this->actingAs($this->admin())
            ->get(route('statistics.payments'))
            ->assertOk()
            ->assertSee('Payment')
            ->assertSee('success rate', false);
    }

    public function test_guest_cannot_reach_payments_page(): void
    {
        $this->get(route('statistics.payments'))->assertRedirect();
    }
}
