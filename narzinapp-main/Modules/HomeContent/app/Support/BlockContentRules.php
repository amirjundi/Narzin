<?php

namespace Modules\HomeContent\Support;

use Closure;

class BlockContentRules
{
    public const ICONS = ['truck', 'shield', 'star', 'returns', 'support', 'tag'];

    public static function for(string $type): array
    {
        $color = ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'];

        return match ($type) {
            'announcement_bar' => self::i18n('content.text')
                + self::link('content.link')
                + ['content.bg_color' => $color, 'content.text_color' => $color],

            'popup' => self::i18n('content.title')
                + self::i18nOptional('content.text')
                + self::i18nOptional('content.button_label')
                + self::link('content.link')
                + [
                    'content.image' => ['nullable', 'string'],
                    'content.frequency.mode' => ['required', 'in:once_per_session,once_per_days'],
                    'content.frequency.days' => ['required_if:content.frequency.mode,once_per_days', 'nullable', 'integer', 'min:1', 'max:90'],
                    'content.delay_seconds' => ['nullable', 'integer', 'min:0', 'max:60'],
                ],

            'hero_slider' => [
                'content.slides' => ['required', 'array', 'min:1', 'max:8'],
                'content.slides.*.image_web' => ['nullable', 'string'],
                'content.slides.*.image_app' => ['nullable', 'string'],
                'content.slides.*.starts_at' => ['nullable', 'date'],
                'content.slides.*.ends_at' => ['nullable', 'date'],
            ]
                + self::i18nOptional('content.slides.*.title')
                + self::i18nOptional('content.slides.*.subtitle')
                + self::link('content.slides.*.link'),

            'category_grid' => [
                'content.category_ids' => ['required', 'array', 'min:2', 'max:20'],
                'content.category_ids.*' => ['integer'],
            ],

            'product_rail' => self::i18n('content.title') + [
                'content.rule' => ['required', 'in:newest,best_sellers,category,manual'],
                'content.category_id' => ['required_if:content.rule,category', 'nullable', 'integer'],
                'content.product_ids' => ['required_if:content.rule,manual', 'array', 'max:24'],
                'content.product_ids.*' => ['integer'],
                'content.limit' => ['nullable', 'integer', 'min:2', 'max:24'],
            ],

            'countdown_banner' => self::i18n('content.text')
                + self::link('content.link')
                + [
                    'content.ends_at_display' => ['required', 'date', 'after:now'],
                    'content.image' => ['nullable', 'string'],
                    'content.bg_color' => $color,
                    'content.text_color' => $color,
                ],

            'info_strip' => [
                'content.items' => ['required', 'array', 'min:2', 'max:4'],
                'content.items.*.icon' => ['required', 'in:' . implode(',', self::ICONS)],
            ]
                + self::i18n('content.items.*.text')
                + self::link('content.items.*.link'),

            'promo_tiles' => [
                'content.tiles' => ['required', 'array', 'min:1', 'max:3'],
                'content.tiles.*.image' => ['nullable', 'string'],
            ]
                + self::i18nOptional('content.tiles.*.label')
                + self::link('content.tiles.*.link'),

            default => [],
        };
    }

    public static function files(string $type): array
    {
        $image = ['nullable', 'image', 'max:4096'];

        return match ($type) {
            'popup' => ['popup_image' => $image],
            'countdown_banner' => ['countdown_image' => $image],
            'hero_slider' => ['slide_images_web.*' => $image, 'slide_images_app.*' => $image],
            'promo_tiles' => ['tile_images.*' => $image],
            default => [],
        };
    }

    private static function i18n(string $field): array
    {
        return [
            $field => ['required', 'array', self::atLeastOneLocale()],
        ] + self::localeStrings($field);
    }

    private static function i18nOptional(string $field): array
    {
        return [$field => ['nullable', 'array']] + self::localeStrings($field);
    }

    private static function localeStrings(string $field): array
    {
        $rules = [];
        foreach (Locale::SUPPORTED as $code) {
            $rules["$field.$code"] = ['nullable', 'string', 'max:500'];
        }

        return $rules;
    }

    private static function link(string $field): array
    {
        return [
            $field => ['nullable', 'array'],
            "$field.type" => ['nullable', 'in:none,category,product,url'],
            "$field.value" => ['nullable'],
        ];
    }

    private static function atLeastOneLocale(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) {
            if (!is_array($value)) {
                $fail("The $attribute field must be an array.");

                return;
            }
            foreach (Locale::SUPPORTED as $code) {
                if (!empty($value[$code])) {
                    return;
                }
            }
            $fail("At least one language must be filled for $attribute.");
        };
    }
}
