<?php

namespace Modules\HomeContent\Support;

class Translatable
{
    public static function resolve(mixed $value, string $locale): ?string
    {
        if (is_string($value)) {
            return $value === '' ? null : $value;
        }
        if (!is_array($value)) {
            return null;
        }
        foreach (array_unique(array_merge([$locale], Locale::SUPPORTED)) as $candidate) {
            if (!empty($value[$candidate]) && is_string($value[$candidate])) {
                return $value[$candidate];
            }
        }

        return null;
    }
}
