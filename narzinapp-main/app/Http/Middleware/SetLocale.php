<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        // Check priority: Session -> Auth User -> Cookie -> Default
        $locale = session('locale');  // First check session
        
        if (!$locale) {
            if (Auth::check() && Auth::user()->preferred_language) {
                $locale = Auth::user()->preferred_language;
            } elseif ($request->cookie('locale')) {
                $locale = $request->cookie('locale');
            } elseif ($request->header('X-Locale')) {
                $locale = $request->header('X-Locale');
            } else {
                $locale = 'ar'; // Default
            }
        }

        // Store in session for subsequent requests
        session(['locale' => $locale]);
        
        // Set the application locale
        app()->setLocale($locale);

        return $next($request);
    }
}

