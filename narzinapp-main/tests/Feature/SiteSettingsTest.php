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
}
