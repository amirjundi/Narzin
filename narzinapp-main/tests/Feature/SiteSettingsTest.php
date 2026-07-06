<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Admin\Models\SiteSetting;
use Tests\TestCase;

class SiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_returns_value_and_default(): void
    {
        SiteSetting::create(['key' => 'whatsapp_number', 'value' => '+964770', 'is_public' => true]);

        $this->assertSame('+964770', SiteSetting::get('whatsapp_number'));
        $this->assertSame('fallback', SiteSetting::get('missing', 'fallback'));
    }

    public function test_public_settings_only_returns_public_rows(): void
    {
        SiteSetting::create(['key' => 'whatsapp_number', 'value' => '111', 'is_public' => true]);
        SiteSetting::create(['key' => 'secret', 'value' => 'shh', 'is_public' => false]);

        $public = SiteSetting::publicSettings();

        $this->assertSame(['whatsapp_number' => '111'], $public);
    }

    public function test_flush_cache_reflects_updates(): void
    {
        SiteSetting::create(['key' => 'whatsapp_number', 'value' => 'old', 'is_public' => true]);
        $this->assertSame('old', SiteSetting::get('whatsapp_number'));

        SiteSetting::where('key', 'whatsapp_number')->update(['value' => 'new']);
        SiteSetting::flushCache();

        $this->assertSame('new', SiteSetting::get('whatsapp_number'));
    }

    public function test_public_api_returns_only_public_settings(): void
    {
        SiteSetting::create(['key' => 'whatsapp_number', 'value' => '+964770123', 'is_public' => true]);
        SiteSetting::create(['key' => 'secret', 'value' => 'shh', 'is_public' => false]);

        $this->getJson('/api/v1/settings/public')
            ->assertOk()
            ->assertJsonPath('data.whatsapp_number', '+964770123')
            ->assertJsonMissingPath('data.secret');
    }

    private function admin(): \App\Models\User
    {
        $user = \App\Models\User::create([
            'name' => 'A', 'email' => 'a' . uniqid() . '@t.test',
            'password' => 'x', 'email_verified_at' => now(),
        ]);
        \Modules\Admin\Models\UserAdmin::create(['user_id' => $user->id, 'is_active' => 1]);

        return $user;
    }

    public function test_guests_cannot_reach_settings_page(): void
    {
        $this->get(route('settings.edit'))->assertRedirect();
    }

    public function test_admin_can_save_whatsapp_number(): void
    {
        $this->actingAs($this->admin())
            ->post(route('settings.update'), [
                'whatsapp_number' => '+964 770-123-4567',
                'support_hours' => 'Sun-Thu 9-18',
            ])
            ->assertRedirect(route('settings.edit'));

        $this->assertSame('+964 770-123-4567', SiteSetting::get('whatsapp_number'));
        $this->assertSame('Sun-Thu 9-18', SiteSetting::get('support_hours'));
    }
}
