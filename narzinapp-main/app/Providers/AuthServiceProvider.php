<?php 

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Add this to ensure session is started and stored
        Auth::authenticated(function ($user) {
            if (request()->hasSession()) {
                request()->session()->put('user_id', $user->id);
                request()->session()->save();
            }
        });
    }
}