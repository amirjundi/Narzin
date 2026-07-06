<?php

namespace Modules\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $table = 'site_settings';

    protected $fillable = ['key', 'value', 'is_public', 'group'];

    protected $casts = ['is_public' => 'boolean'];

    public static function get(string $key, $default = null)
    {
        $all = Cache::rememberForever('site_settings.all', fn () => static::pluck('value', 'key')->all());

        return $all[$key] ?? $default;
    }

    public static function publicSettings(): array
    {
        return Cache::rememberForever(
            'site_settings.public',
            fn () => static::where('is_public', true)->pluck('value', 'key')->all()
        );
    }

    public static function flushCache(): void
    {
        Cache::forget('site_settings.all');
        Cache::forget('site_settings.public');
    }
}
