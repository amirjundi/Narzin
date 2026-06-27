<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Modules\Admin\Http\Middleware\AdminMiddleware;
use Modules\ProductManagement\Http\Middleware\VendorAccountMiddleware;
use Modules\ProductManagement\Http\Middleware\VendorProductOwnershipMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware) {
        $middleware->group('api', [
            \Illuminate\Http\Middleware\HandleCors::class,
            EnsureFrontendRequestsAreStateful::class,
            'throttle:60,1',  // 60 requests per minute
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
        $middleware->group('web', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\SetLocale::class,  // Add SetLocale middleware here

        ]);


        $middleware->alias([
            'vendor.account' => VendorAccountMiddleware::class,
            'vendor.product' => VendorProductOwnershipMiddleware::class,
            'admin.auth' => AdminMiddleware::class,
        ]);

        // Global Middleware
        $middleware->append(\App\Http\Middleware\SetLocale::class);
    })

    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthenticated',
                    'errors' => ['auth' => ['Please login to access this resource']]
                ], 401);
            }
        });

        // Defence-in-depth: never expose database/internal error details to API
        // clients. Database errors in particular leak schema and query text.
        $exceptions->render(function (\Illuminate\Database\QueryException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                \Illuminate\Support\Facades\Log::error('Database error', [
                    'message' => $e->getMessage(),
                    'url' => $request->fullUrl(),
                ]);

                return response()->json([
                    'status' => false,
                    'message' => config('app.debug')
                        ? $e->getMessage()
                        : 'A server error occurred. Please try again later.',
                ], 500);
            }
        });
    })->create();
