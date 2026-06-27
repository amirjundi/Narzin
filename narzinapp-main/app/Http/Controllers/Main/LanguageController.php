<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LanguageController extends Controller
{
    public function switchLanguage(Request $request, $locale)
    {
        // Validate the language
        $languages = ['ar', 'nl'];
        if (!in_array($locale, $languages)) {
            abort(400, 'Invalid Language');
        }

        // Save preference in session immediately
        session(['locale' => $locale]);
        
        // If user is logged in, save preference in the database
        if (Auth::check()) {
            $user = User::find(Auth::id());
            $user->preferred_language = $locale;
            $user->save();
        } else {
            // Set cookie
            cookie()->queue('locale', $locale, 60 * 24 * 30);
        }

        // Force the application locale
        app()->setLocale($locale);

        // Return JSON response instead of redirecting
        return response()->json([
            'success' => true,
            'locale' => app()->getLocale()
        ]);
    }
}