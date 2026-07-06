<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Admin\Models\SiteSetting;

class SiteSettingController extends Controller
{
    /** Public storefront read of whitelisted settings. */
    public function publicIndex(): JsonResponse
    {
        return response()->json(['data' => SiteSetting::publicSettings()]);
    }
}
