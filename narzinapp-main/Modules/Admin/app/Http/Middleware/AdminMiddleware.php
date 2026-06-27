<?php

namespace Modules\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Admin\Models\UserAdmin;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $isAdmin = UserAdmin::query()
                ->where('user_id', $user->id)
                ->where('is_active', 1)
                ->exists();
            
            if ($isAdmin) {
                return $next($request);
            }
        }

        // Return JSON for API requests, redirect for web requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        if (Auth::check()) {
            abort(403, 'Unauthorized. Admin access required.');
        }

        return redirect()->route('login');
    }
}
