<?php

namespace Tests\Unit;

use Modules\HomeContent\Support\ImageUrl;
use Modules\HomeContent\Support\Link;
use Modules\HomeContent\Support\Locale;
use Modules\HomeContent\Support\Translatable;
use Tests\TestCase;

class HomeContentSupportTest extends TestCase
{
    public function test_locale_normalization(): void
    {
        $this->assertSame('de', Locale::normalize('du'));
        $this->assertSame('de', Locale::normalize('DE'));
        $this->assertSame('en', Locale::normalize('en'));
        $this->assertSame('ar', Locale::normalize('fr'));
        $this->assertSame('ar', Locale::normalize(null));
    }

    public function test_translatable_picks_requested_locale(): void
    {
        $value = ['ar' => 'نص', 'de' => 'Text', 'en' => 'text'];
        $this->assertSame('Text', Translatable::resolve($value, 'de'));
    }

    public function test_translatable_falls_back_in_order(): void
    {
        $this->assertSame('نص', Translatable::resolve(['ar' => 'نص'], 'en'));
        $this->assertSame('Text', Translatable::resolve(['de' => 'Text'], 'en'));
        $this->assertNull(Translatable::resolve(['ar' => '', 'de' => ''], 'ar'));
        $this->assertNull(Translatable::resolve(null, 'ar'));
        $this->assertSame('plain', Translatable::resolve('plain', 'de'));
    }

    public function test_link_resolution(): void
    {
        $this->assertSame(['type' => 'category', 'value' => 7], Link::resolve(['type' => 'category', 'value' => '7']));
        $this->assertSame(['type' => 'url', 'value' => 'https://x.test/a'], Link::resolve(['type' => 'url', 'value' => 'https://x.test/a']));
        $this->assertNull(Link::resolve(['type' => 'none']));
        $this->assertNull(Link::resolve(['type' => 'url', 'value' => 'not a url']));
        $this->assertNull(Link::resolve(['type' => 'product', 'value' => 'abc']));
        $this->assertNull(Link::resolve('nope'));
        $this->assertNull(Link::resolve(['type' => 'url', 'value' => 'ftp://x.test/a']));
    }

    public function test_image_url(): void
    {
        config(['app.url' => 'https://api.test']);
        $this->assertSame('https://api.test/storage/homeBlocks/a.jpg', ImageUrl::make('homeBlocks/a.jpg'));
        $this->assertSame('https://cdn.test/b.jpg', ImageUrl::make('https://cdn.test/b.jpg'));
        $this->assertNull(ImageUrl::make(null));
        $this->assertNull(ImageUrl::make(''));
    }
}
