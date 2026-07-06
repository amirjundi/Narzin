<?php

namespace Modules\HomeContent\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\HomeContent\Services\HomeFeedService;
use Modules\HomeContent\Services\ProductRailResolver;
use Modules\HomeContent\Support\Locale;
use Modules\ProductManagement\Models\Product;
use Modules\Telemetry\Models\UserProductView;

class HomeController extends Controller
{
    public function index(Request $request, HomeFeedService $service): JsonResponse
    {
        $platform = $request->query('platform', 'web');
        if (!in_array($platform, ['web', 'app'], true)) {
            return response()->json(['status' => false, 'message' => 'platform must be web or app'], 422);
        }

        $locale = Locale::normalize($request->query('locale'));

        $token = (string) config('homecontent.preview_token');
        $preview = $request->boolean('preview')
            && $token !== ''
            && hash_equals($token, (string) $request->query('preview_token'));

        return response()->json([
            'status' => true,
            'data' => $service->feed($platform, $locale, $preview),
        ]);
    }

    /**
     * Personalized "For You" rails derived from the visitor's product-view
     * history (Telemetry). Returns product_rail blocks in the same shape as the
     * home feed so the storefront can render them with the existing renderer.
     * Not cached — it is per-visitor. Empty for visitors with no history.
     */
    public function forYou(Request $request, ProductRailResolver $rails): JsonResponse
    {
        $locale = Locale::normalize($request->query('locale'));
        $sessionId = (string) $request->query('session_id', '');
        $userId = auth('sanctum')->id();

        $views = UserProductView::query();
        if ($userId) {
            $views->where('user_id', $userId);
        } elseif ($sessionId !== '') {
            $views->where('session_id', $sessionId)->whereNull('user_id');
        } else {
            return response()->json(['status' => true, 'data' => []]);
        }

        $viewedIds = $views->orderByDesc('updated_at')
            ->limit(40)
            ->pluck('product_id')
            ->unique()
            ->values();

        if ($viewedIds->isEmpty()) {
            return response()->json(['status' => true, 'data' => []]);
        }

        $out = [];
        $id = 2000;

        // Recently viewed (in the order they were last seen)
        $recent = $rails->resolve(['rule' => 'manual', 'product_ids' => $viewedIds->take(12)->all()]);
        if (! empty($recent)) {
            $out[] = ['id' => $id++, 'type' => 'product_rail', 'content' => [
                'title'    => $locale === 'ar' ? 'شاهدت مؤخرًا' : ($locale === 'de' ? 'Kürzlich angesehen' : 'Recently Viewed'),
                'rule'     => 'manual',
                'products' => $recent,
            ]];
        }

        // Recommended: other in-stock products from the categories you browse.
        $catIds = Product::whereIn('id', $viewedIds)->pluck('category_id')->filter()->unique();
        if ($catIds->isNotEmpty()) {
            $recIds = Product::where('is_active', true)
                ->whereIn('category_id', $catIds)
                ->whereNotIn('id', $viewedIds)
                ->whereHas('variants', fn ($q) => $q->where('is_active', true)->where('is_out_of_stock', false))
                ->inRandomOrder()
                ->limit(12)
                ->pluck('id')
                ->all();

            if (! empty($recIds)) {
                $rec = $rails->resolve(['rule' => 'manual', 'product_ids' => $recIds]);
                if (! empty($rec)) {
                    $out[] = ['id' => $id++, 'type' => 'product_rail', 'content' => [
                        'title'    => $locale === 'ar' ? 'مختار لك' : ($locale === 'de' ? 'Für dich' : 'Recommended for You'),
                        'rule'     => 'manual',
                        'products' => $rec,
                    ]];
                }
            }
        }

        return response()->json(['status' => true, 'data' => $out]);
    }
}
