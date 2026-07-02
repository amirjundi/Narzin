<?php

namespace Modules\HomeContent\Support;

class Link
{
    public static function resolve(mixed $link): ?array
    {
        if (!is_array($link)) {
            return null;
        }
        $type = $link['type'] ?? 'none';
        $value = $link['value'] ?? null;

        if ($type === 'url') {
            $isValid = filter_var($value, FILTER_VALIDATE_URL) && preg_match('#^https?://#i', (string) $value);

            return $isValid ? ['type' => 'url', 'value' => $value] : null;
        }
        if (in_array($type, ['category', 'product'], true)) {
            return is_numeric($value) ? ['type' => $type, 'value' => (int) $value] : null;
        }

        return null;
    }
}
