<?php

namespace Modules\HomeContent\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\HomeContent\Models\HomeBlock;
use Modules\HomeContent\Support\ImageUrl;
use Modules\HomeContent\Support\Link;
use Modules\HomeContent\Support\Locale;
use Modules\HomeContent\Support\Translatable;
use Modules\ProductManagement\Models\Category;

class HomeFeedService
{
    public function __construct(private readonly ProductRailResolver $rails)
    {
    }

    public static function cacheKey(string $platform, string $locale): string
    {
        return "home:v1:{$platform}:{$locale}";
    }

    public static function flushCache(): void
    {
        foreach (['web', 'app'] as $platform) {
            foreach (Locale::SUPPORTED as $locale) {
                Cache::forget(self::cacheKey($platform, $locale));
            }
        }
    }

    public function feed(string $platform, string $locale, bool $preview = false): array
    {
        if ($preview) {
            return $this->build($platform, $locale, true);
        }

        return Cache::remember(
            self::cacheKey($platform, $locale),
            300,
            fn () => $this->build($platform, $locale, false)
        );
    }

    private function build(string $platform, string $locale, bool $preview): array
    {
        $now = now();
        $query = HomeBlock::query()
            ->where('is_active', true)
            ->whereIn('platform', ['both', $platform])
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
            ->orderBy('sort_order')->orderBy('id');

        if (!$preview) {
            $query->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now));
        }

        $out = [];
        foreach ($query->get() as $block) {
            try {
                $content = $this->resolveContent($block, $platform, $locale);
            } catch (\Throwable $e) {
                Log::warning("home_blocks: skipping block {$block->id} ({$block->type}): {$e->getMessage()}");
                continue;
            }
            if ($content === null) {
                continue;
            }
            $item = ['id' => $block->id, 'type' => $block->type, 'content' => $content];
            if ($preview && $block->starts_at && $block->starts_at->isFuture()) {
                $item['preview_upcoming'] = true;
            }
            $out[] = $item;
        }

        return $out;
    }

    private function resolveContent(HomeBlock $block, string $platform, string $locale): ?array
    {
        $c = $block->content ?? [];

        return match ($block->type) {
            'announcement_bar' => $this->announcementBar($c, $locale),
            'popup' => $this->popup($c, $locale),
            'hero_slider' => $this->heroSlider($c, $platform, $locale),
            'category_grid' => $this->categoryGrid($c, $locale),
            'product_rail' => $this->productRail($c, $locale),
            'countdown_banner' => $this->countdownBanner($c, $locale),
            'info_strip' => $this->infoStrip($c, $locale),
            'promo_tiles' => $this->promoTiles($c, $locale),
            default => null,
        };
    }

    private function announcementBar(array $c, string $locale): ?array
    {
        $text = Translatable::resolve($c['text'] ?? null, $locale);
        if ($text === null) {
            return null;
        }

        return [
            'text' => $text,
            'link' => Link::resolve($c['link'] ?? null),
            'bg_color' => $c['bg_color'] ?? '#141923',
            'text_color' => $c['text_color'] ?? '#C5A880',
        ];
    }

    private function popup(array $c, string $locale): ?array
    {
        $title = Translatable::resolve($c['title'] ?? null, $locale);
        if ($title === null) {
            return null;
        }

        return [
            'image' => ImageUrl::make($c['image'] ?? null),
            'title' => $title,
            'text' => Translatable::resolve($c['text'] ?? null, $locale),
            'button_label' => Translatable::resolve($c['button_label'] ?? null, $locale),
            'link' => Link::resolve($c['link'] ?? null),
            'frequency' => [
                'mode' => $c['frequency']['mode'] ?? 'once_per_session',
                'days' => (int) ($c['frequency']['days'] ?? 0),
            ],
            'delay_seconds' => (int) ($c['delay_seconds'] ?? 3),
        ];
    }

    private function heroSlider(array $c, string $platform, string $locale): ?array
    {
        $now = now();
        $slides = collect(is_array($c['slides'] ?? null) ? $c['slides'] : [])
            ->filter(function ($slide) use ($now) {
                if (!is_array($slide)) {
                    return false;
                }
                if (!empty($slide['starts_at']) && $now->lt(Carbon::parse($slide['starts_at']))) {
                    return false;
                }
                if (!empty($slide['ends_at']) && $now->gt(Carbon::parse($slide['ends_at']))) {
                    return false;
                }

                return true;
            })
            ->map(function (array $slide) use ($platform, $locale) {
                $image = $platform === 'app'
                    ? ($slide['image_app'] ?? $slide['image_web'] ?? null)
                    : ($slide['image_web'] ?? $slide['image_app'] ?? null);
                if (!$image) {
                    return null;
                }

                return [
                    'image' => ImageUrl::make($image),
                    'title' => Translatable::resolve($slide['title'] ?? null, $locale),
                    'subtitle' => Translatable::resolve($slide['subtitle'] ?? null, $locale),
                    'link' => Link::resolve($slide['link'] ?? null),
                ];
            })
            ->filter()
            ->values();

        return $slides->isEmpty() ? null : ['slides' => $slides->all()];
    }

    private function categoryGrid(array $c, string $locale): ?array
    {
        $ids = array_map('intval', is_array($c['category_ids'] ?? null) ? $c['category_ids'] : []);
        if ($ids === []) {
            return null;
        }
        // Category's default global scope does CONCAT(app.url, image) in SQL,
        // which MySQL supports but sqlite (used in tests) does not. Fetch the raw
        // image column without that scope and build the same URL in PHP instead
        // (mirrors the workaround already applied in ProductRailResolver).
        $categories = Category::withoutGlobalScope('image_url')
            ->whereIn('id', $ids)->get()
            ->sortBy(fn ($cat) => array_search($cat->id, $ids, true))
            ->values();
        if ($categories->isEmpty()) {
            return null;
        }

        return [
            'categories' => $categories->map(fn ($cat) => [
                'id' => $cat->id,
                'name' => $locale === 'ar'
                    ? ($cat->name_arabic ?: $cat->name_german)
                    : ($cat->name_german ?: $cat->name_arabic),
                'image' => ImageUrl::make($cat->image),
            ])->all(),
        ];
    }

    private function productRail(array $c, string $locale): ?array
    {
        $products = $this->rails->resolve($c);
        if ($products === []) {
            return null;
        }

        return [
            'title' => Translatable::resolve($c['title'] ?? null, $locale),
            'rule' => $c['rule'] ?? 'newest',
            'products' => $products,
        ];
    }

    private function countdownBanner(array $c, string $locale): ?array
    {
        $ends = !empty($c['ends_at_display']) ? Carbon::parse($c['ends_at_display']) : null;
        if ($ends === null || $ends->isPast()) {
            return null;
        }
        $text = Translatable::resolve($c['text'] ?? null, $locale);
        if ($text === null) {
            return null;
        }

        return [
            'text' => $text,
            'ends_at' => $ends->toIso8601String(),
            'link' => Link::resolve($c['link'] ?? null),
            'image' => ImageUrl::make($c['image'] ?? null),
            'bg_color' => $c['bg_color'] ?? '#141923',
            'text_color' => $c['text_color'] ?? '#D4AF37',
        ];
    }

    private function infoStrip(array $c, string $locale): ?array
    {
        $items = collect(is_array($c['items'] ?? null) ? $c['items'] : [])
            ->map(function ($item) use ($locale) {
                if (!is_array($item)) {
                    return null;
                }
                $text = Translatable::resolve($item['text'] ?? null, $locale);
                if ($text === null) {
                    return null;
                }

                return [
                    'icon' => $item['icon'] ?? 'tag',
                    'text' => $text,
                    'link' => Link::resolve($item['link'] ?? null),
                ];
            })
            ->filter()
            ->values();

        return $items->isEmpty() ? null : ['items' => $items->all()];
    }

    private function promoTiles(array $c, string $locale): ?array
    {
        $tiles = collect(is_array($c['tiles'] ?? null) ? $c['tiles'] : [])
            ->map(function ($tile) use ($locale) {
                if (!is_array($tile) || empty($tile['image'])) {
                    return null;
                }

                return [
                    'image' => ImageUrl::make($tile['image']),
                    'label' => Translatable::resolve($tile['label'] ?? null, $locale),
                    'link' => Link::resolve($tile['link'] ?? null),
                ];
            })
            ->filter()
            ->values();

        return $tiles->isEmpty() ? null : ['tiles' => $tiles->all()];
    }
}
