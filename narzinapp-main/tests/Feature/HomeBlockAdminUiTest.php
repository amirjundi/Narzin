<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Admin\Models\UserAdmin;
use Modules\HomeContent\Models\HomeBlock;
use Tests\TestCase;

class HomeBlockAdminUiTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::create([
            'name' => 'A', 'email' => 'a' . uniqid() . '@t.test',
            'password' => 'x', 'email_verified_at' => now(),
        ]);
        UserAdmin::create(['user_id' => $user->id, 'is_active' => 1]);

        return $user;
    }

    public function test_index_lists_blocks_with_controls(): void
    {
        HomeBlock::create([
            'type' => 'announcement_bar', 'name' => 'Summer promo', 'platform' => 'web',
            'is_active' => true, 'sort_order' => 1, 'content' => ['text' => ['ar' => 'x']],
        ]);

        $this->actingAs($this->admin())
            ->get(route('home-blocks.index'))
            ->assertOk()
            ->assertSee('Homepage Builder')
            ->assertSee('Summer promo')
            ->assertSee('announcement bar')
            ->assertSee('Add block');
    }

    public function test_legacy_admin_pages_redirect_to_builder(): void
    {
        $this->actingAs($this->admin())->get('/banners')->assertRedirect(route('home-blocks.index'));
        $this->actingAs($this->admin())->get('/before-nav')->assertRedirect(route('home-blocks.index'));
    }
}
