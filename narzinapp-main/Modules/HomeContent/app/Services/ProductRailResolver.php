<?php

namespace Modules\HomeContent\Services;

use Modules\Admin\Models\PlatformMarkup;
use Modules\Admin\Models\PriceExchange;
use Modules\Checkout\Models\OrderItem;
use Modules\ProductManagement\Models\Product;
use Modules\ProductManagement\Models\ProductImage;
use Modules\ProductManagement\Models\ProductVariant;

class ProductRailResolver
{
    public const RULES = ['newest', 'best_sellers', 'category', 'manual', 'random'];

    public function resolve(array $content, int $minCount = 0): array
    {
        $rule = $content['rule'] ?? 'newest';
        $limit = min(max((int) ($content['limit'] ?? 12), 1), 24);

        $rate = (float) (PriceExchange::latest('created_at')->first()->price_rate ?? 1);
        $rate = $rate > 0 ? $rate : 1.0;
        $globalMarkup = (float) PlatformMarkup::getLatest();

        $query = Product::with(['vendor'])
            ->where('products.is_active', true)
            ->whereHas('variants', fn ($q) => $q
                ->where('is_active', true)
                ->where('is_out_of_stock', false))
            ->select('products.*')
            ->addSelect([
                'min_price' => ProductVariant::selectRaw('price / ?', [$rate])
                    ->whereColumn('product_id', 'products.id')
                    ->where('is_active', true)
                    ->where('is_out_of_stock', false)
                    ->orderBy('price')
                    ->limit(1),
                'min_price_variant_id' => ProductVariant::select('id')
                    ->whereColumn('product_id', 'products.id')
                    ->where('is_active', true)
                    ->where('is_out_of_stock', false)
                    ->orderBy('price')
                    ->limit(1),
            ]);

        switch ($rule) {
            case 'best_sellers':
                $query->addSelect([
                    'units_sold' => OrderItem::selectRaw('COALESCE(SUM(quantity), 0)')
                        ->whereColumn('order_items.product_id', 'products.id'),
                ])->orderByDesc('units_sold');
                break;

            case 'category':
                $categoryId = (int) ($content['category_id'] ?? 0);
                $query->where(fn ($q) => $q
                    ->where('category_id', $categoryId)
                    ->orWhere('child_category_id', $categoryId))
                    ->latest();
                break;

            case 'manual':
                $ids = array_map('intval', $content['product_ids'] ?? []);
                if ($ids === []) {
                    return [];
                }
                $query->whereIn('products.id', $ids);
                break;

            case 'random':
                $query->inRandomOrder();
                break;

            default:
                $query->latest();
        }

        if ($rule === 'manual') {
            // SQL LIMIT has no ORDER BY guarantee here, so truncating in SQL could
            // arbitrarily drop products the admin intended to keep. Fetch all matches,
            // sort by admin order in PHP, then truncate.
            $ids = array_map('intval', $content['product_ids']);
            $products = $query->get()
                ->sortBy(fn ($p) => array_search($p->id, $ids, true))
                ->values()
                ->take($limit);
        } else {
            $products = $query->limit($limit)->get();
        }

        // ProductImage has a global scope that does CONCAT(app.url, image) in SQL,
        // which MySQL supports but sqlite (used in tests) does not. Fetch the raw
        // image column without that scope and build the same URL in PHP instead.
        $images = ProductImage::withoutGlobalScope('image_url')
            ->whereIn('product_id', $products->pluck('id'))
            ->get()
            ->groupBy('product_id');
        $appUrl = (string) config('app.url');

        return $products
            ->map(function (Product $product) use ($globalMarkup, $rate, $images, $appUrl) {
                if ($product->min_price === null) {
                    return null;
                }
                $vendor = $product->vendor;
                $markup = ($vendor && $vendor->markup_percentage !== null)
                    ? (float) $vendor->markup_percentage
                    : $globalMarkup;
                $eur = round(((float) $product->min_price) * (1 + $markup / 100), 2);

                $imagePath = $images->get($product->id)?->first()?->image;

                return [
                    'id' => $product->id,
                    'name_arabic' => $product->name_arabic,
                    'name_german' => $product->name_german,
                    'slug_arabic' => $product->slug_arabic,
                    'slug_german' => $product->slug_german,
                    'image' => $imagePath ? $appUrl . '/storage/' . $imagePath : null,
                    'min_price' => $eur,
                    'min_price_iqd' => (int) round($eur * $rate),
                    'min_price_variant_id' => $product->min_price_variant_id,
                ];
            })
            ->filter()
            ->values()
            ->all();

        // Enforce minimum product count — callers like autoFeed() use this
        // to suppress sections that would show with too few products.
        if ($minCount > 0 && count($result) < $minCount) {
            return [];
        }

        return $result;
    }
}
