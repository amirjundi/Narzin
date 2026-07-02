<?php

namespace Modules\HomeContent\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\HomeContent\Services\HomeFeedService;

class HomeBlock extends Model
{
    public const TYPES = [
        'announcement_bar',
        'popup',
        'hero_slider',
        'category_grid',
        'product_rail',
        'countdown_banner',
        'info_strip',
        'promo_tiles',
    ];

    public const PLATFORMS = ['web', 'app', 'both'];

    protected $fillable = [
        'type', 'name', 'sort_order', 'is_active', 'platform', 'starts_at', 'ends_at', 'content',
    ];

    protected $casts = [
        'content' => 'array',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => HomeFeedService::flushCache());
        static::deleted(fn () => HomeFeedService::flushCache());
    }

    public function scopeVisible(Builder $query, string $platform): Builder
    {
        $now = now();

        return $query->where('is_active', true)
            ->whereIn('platform', ['both', $platform])
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
