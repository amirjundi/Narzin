<?php

namespace App\Http\Controllers\V1\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
class LogoutController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            // Validate that the user is actually authenticated
            if (!Auth::check()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Not authenticated',
                    'errors' => ['auth' => ['User not logged in']]
                ], Response::HTTP_UNAUTHORIZED);
            }



            // Token clients (mobile): revoke the current personal access token.
            // Cookie/session clients (web SPA) have no access token, so this is
            // skipped and the session is invalidated below instead.
            $currentToken = $request->user()->currentAccessToken();

            if ($currentToken && method_exists($currentToken, 'delete')) {
                try {
                    $currentToken->delete();
                } catch (\Exception $e) {
                    Log::error('Token deletion failed', [
                        'user_id' => $request->user()->id,
                        'token_id' => $currentToken->id,
                        'error' => $e->getMessage()
                    ]);

                    return response()->json([
                        'status' => false,
                        'message' => 'Failed to delete token',
                        'errors' => ['token' => ['Unable to delete the token']]
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }

            // Log the session-based (SPA) user out of the web guard.
            if ($request->hasSession()) {
                Auth::guard('web')->logout();
            }

            // Clear any session data if exists
            if ($request->hasSession()) {
                try {
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                } catch (\Exception $e) {
                    Log::warning('Session cleanup failed', [
                        'user_id' => $request->user()->id,
                        'error' => $e->getMessage()
                    ]);
                    // Continue execution as session cleanup is not critical
                }
            }

            // Log the successful logout
            Log::info('User logged out successfully', [
                'user_id' => $request->user()->id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Successfully logged out',
                'data' => [
                    'logged_out_at' => now()->toIso8601String(),
                    'device_info' => [
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent()
                    ]
                ]
            ], Response::HTTP_OK);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error during logout', [
                'error' => $e->getMessage(),
                'sql' => $e->getSql() ?? 'No SQL available',
                'bindings' => $e->getBindings() ?? []
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Database error occurred',
                'errors' => ['database' => ['A database error occurred while processing your request']]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);

        } catch (\Exception $e) {
            Log::error('Unexpected error during logout', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'An unexpected error occurred',
                'errors' => ['general' => ['An unexpected error occurred during logout']]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
