<?php

namespace Modules\Banners\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Admin\Models\Banner;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function indexMobile()
    {
        try {
            $banners = Banner::where('is_mobile', 1)->get();
            $baseUrl = config('app.url');

            $banners->transform(function ($banner) use ($baseUrl) {
                $banner->image = $baseUrl . '/storage/' . $banner->image;
                return $banner;
            });

            return response()->json([
                'status' => true,
                'data' => $banners
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function indexWeb(){
        try {
            $banners = Banner::where('is_mobile', 0)->get();
            $baseUrl = config('app.url');

            $banners->transform(function ($banner) use ($baseUrl) {
                $banner->image = $baseUrl . '/storage/' . $banner->image;
                return $banner;
            });

            return response()->json([
                'status' => true,
                'data' => $banners
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
