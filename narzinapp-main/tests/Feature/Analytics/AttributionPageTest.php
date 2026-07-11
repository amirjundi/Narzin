<?php

namespace Tests\Feature\Analytics;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Admin\Models\UserAdmin;
use Tests\TestCase;

class AttributionPageTest extends TestCase
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

    public function test_funnel_page_shows_attribution_section(): void
    {
        $this->actingAs($this->admin())
            ->get(route('statistics.funnel'))
            ->assertOk()
            ->assertSee('Attribution')
            ->assertSee('Campaign');
    }
}
