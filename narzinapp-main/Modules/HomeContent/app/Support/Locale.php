<?php

namespace Modules\HomeContent\Support;

class Locale
{
    public const SUPPORTED = ['ar', 'de', 'en'];

    public static function normalize(?string $code): string
    {
        $code = strtolower((string) $code);
        if ($code === 'du') {
            $code = 'de';
        }

        return in_array($code, self::SUPPORTED, true) ? $code : 'ar';
    }
}
