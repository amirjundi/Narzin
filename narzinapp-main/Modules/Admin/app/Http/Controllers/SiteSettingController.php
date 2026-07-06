<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Admin\Models\SiteSetting;

class SiteSettingController extends Controller
{
    /** Public storefront read of whitelisted settings. */
    public function publicIndex(): JsonResponse
    {
        return response()->json(['data' => SiteSetting::publicSettings()]);
    }

    /** Admin form. */
    public function edit()
    {
        return view('admin::settings.edit', [
            'whatsapp_number' => SiteSetting::get('whatsapp_number'),
            'support_hours' => SiteSetting::get('support_hours'),
        ]);
    }

    /** Persist admin-editable public settings. */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'whatsapp_number' => ['nullable', 'string', 'max:32', 'regex:/^[0-9+\s\-()]*$/'],
            'support_hours' => ['nullable', 'string', 'max:120'],
        ]);

        foreach (['whatsapp_number', 'support_hours'] as $key) {
            SiteSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $validated[$key] ?? null, 'is_public' => true, 'group' => 'contact']
            );
        }
        SiteSetting::flushCache();

        return redirect()->route('settings.edit')->with('success', 'Settings saved.');
    }
}
