<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Modules\Admin\Models\UserAdmin;
use Modules\Vendor\Models\Vendor;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $admin = UserAdmin::where('user_id', Auth::id())->exists();
        if($admin){
            return redirect()->intended(route('admins.index', absolute: false));
        }else{
            $vendor = Vendor::where('user_id', Auth::id())->exists();
            if($vendor){
                return redirect()->intended(route('vendor.dashboard', absolute: false));
            }else{
                return redirect()->intended(route('dashboard', absolute: false));
            }
        }

    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        DB::table('sessions')
        ->where('id', session()->getId())
        ->delete();

        
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
