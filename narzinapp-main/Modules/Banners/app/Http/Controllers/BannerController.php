<?php

namespace Modules\Banners\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\HomeContent\Services\HomeFeedService;

class BannerController extends Controller
{
    public function indexMobile()
    {
        return $this->legacyBanners('app', 1);
    }

    public function indexWeb()
    {
        return $this->legacyBanners('web', 0);
    }

    private function legacyBanners(string $platform, int $isMobile)
    {
        try {
            $feed = app(HomeFeedService::class)->feed($platform, 'ar');

            $banners = collect($feed)
                ->where('type', 'hero_slider')
                ->flatMap(fn ($block) => $block['content']['slides'])
                ->values()
                ->map(fn ($slide, $i) => [
                    'id' => $i + 1,
                    'image' => $slide['image'],
                    'title' => $slide['title'],
                    'description' => $slide['subtitle'],
                    'is_mobile' => $isMobile,
                ]);

            return response()->json(['status' => true, 'data' => $banners]);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
