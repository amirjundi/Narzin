<?php

namespace Modules\HomeContent\Support;

use Illuminate\Support\Str;

class ImageUrl
{
    public static function make(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return rtrim(config('app.url'), '/') . '/storage/' . ltrim($path, '/');
    }
}
