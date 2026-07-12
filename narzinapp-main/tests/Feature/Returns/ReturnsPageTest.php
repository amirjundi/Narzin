<?php

namespace Tests\Feature\Returns;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Admin\Models\UserAdmin;
use Tests\TestCase;

class ReturnsPageTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $u = User::create(['name' => 'A', 'email' => 'a' . uniqid() . '@t.test', 'password' => 'x', 'email_verified_at' => now()]);
        UserAdmin::create(['user_id' => $u->id, 'is_active' => 1]);
        return $u;
    }

    public function test_admin_sees_returns_analytics_page(): void
    {
        $this->actingAs($this->admin())->get(route('statistics.returns'))
            ->assertOk()->assertSee('Returns')->assertSee('Return rate', false);
    }

    public function test_admin_sees_returns_management_list(): void
    {
        $this->actingAs($this->admin())->get(route('returns.index'))
            ->assertOk()->assertSee('Returns');
    }

    public function test_guest_cannot_reach_returns(): void
    {
        $this->get(route('statistics.returns'))->assertRedirect();
        $this->get(route('returns.index'))->assertRedirect();
    }
}
