<?php

namespace Modules\Banners\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\HomeContent\Services\HomeFeedService;

class BeforeNavController extends Controller
{
    public function index()
    {
    }

    public function getCurrent()
    {
        $feed = app(HomeFeedService::class)->feed('web', 'ar');
        $bar = collect($feed)->firstWhere('type', 'announcement_bar');

        if (!$bar) {
            return response()->json([
                'success' => true,
                'message' => 'No active banner found',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Active banner retrieved successfully',
            'data' => [
                'id' => $bar['id'],
                'text' => $bar['content']['text'],
                'start_date' => null,
                'end_date' => null,
            ],
        ], 200);
    }
}
