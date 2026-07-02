<?php

namespace Modules\HomeContent\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\HomeContent\Services\HomeFeedService;
use Modules\HomeContent\Support\Locale;

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
}
