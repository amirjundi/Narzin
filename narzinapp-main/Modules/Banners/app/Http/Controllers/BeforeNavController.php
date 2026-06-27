<?php

namespace Modules\Banners\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BeforeNavController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {}

    /**
     * Get the current active before nav banner
     */
    public function getCurrent()
    {
        $today = Carbon::today()->format('Y-m-d');

        $banner = DB::table('before_nav')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->orderBy('created_at', 'desc')
            ->first();
        if (!$banner) {
            return response()->json([
                'success' => true,
                'message' => 'No active banner found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Active banner retrieved successfully',
            'data' => $banner
        ], 200);
    }
}
