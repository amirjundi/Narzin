<?php

namespace App\Http\Controllers\V1\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $user = $request->user();

            // Get web sessions from Breeze
            $webSessions = DB::table('sessions')
                ->where('user_id', $user->id)
                ->get()
                ->map(function ($session) {
                    $payload = unserialize(base64_decode($session->payload));
                    $userAgent = $payload['_user_agent'] ?? 'Unknown';
                    
                    return [
                        'id' => $session->id,
                        'ip_address' => $session->ip_address,
                        'device_info' => $this->parseUserAgent($userAgent),
                        'last_activity' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                        'is_current' => $session->id === session()->getId(),
                        'type' => 'Web Session'
                    ];
                });

            // Get API tokens from Sanctum
            $apiTokens = $user->tokens()->get()->map(function ($token) use ($request) {
                return [
                    'id' => $token->id,
                    'device_info' => $this->parseUserAgent($token->name),
                    'last_used' => $token->last_used_at ? $token->last_used_at->diffForHumans() : 'Never',
                    'is_current' => $token->id === optional($request->user()->currentAccessToken())->id,
                    'type' => 'API Token',
                    'created_at' => $token->created_at->diffForHumans()
                ];
            });

            // Combine web sessions and API tokens
            $allDevices = $webSessions->concat($apiTokens);

            return response()->json([
                'status' => true,
                'message' => 'Profile retrieved successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'email_verified_at' => $user->email_verified_at,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at
                    ],
                    'devices' => [
                        'current_device' => $this->parseUserAgent($request->userAgent()),
                        'all_devices' => $allDevices,
                        'total_devices' => $allDevices->count(),
                        'web_sessions' => $webSessions->count(),
                        'api_tokens' => $apiTokens->count()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve profile',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    private function parseUserAgent($userAgent)
    {
        if (!$userAgent) {
            return [
                'device_type' => 'Unknown',
                'os' => 'Unknown',
                'browser' => 'Unknown',
                'user_agent' => $userAgent
            ];
        }

        // Device Type Detection
        $deviceType = 'Desktop';
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($userAgent))) {
            $deviceType = 'Tablet';
        } else if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', strtolower($userAgent))) {
            $deviceType = 'Mobile';
        } else if (preg_match('/(postman|insomnia|curl)/i', strtolower($userAgent))) {
            $deviceType = 'API Client';
        }

        // OS Detection
        $os = 'Unknown';
        if (preg_match('/windows|win32|win64/i', $userAgent)) {
            $os = 'Windows';
        } else if (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $os = 'macOS';
        } else if (preg_match('/linux/i', $userAgent)) {
            $os = 'Linux';
        } else if (preg_match('/android/i', $userAgent)) {
            $os = 'Android';
        } else if (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
            $os = 'iOS';
        }

        // Browser Detection
        $browser = 'Unknown';
        if (preg_match('/MSIE|Trident/i', $userAgent)) {
            $browser = 'Internet Explorer';
        } else if (preg_match('/Firefox/i', $userAgent)) {
            $browser = 'Firefox';
        } else if (preg_match('/Chrome/i', $userAgent)) {
            $browser = 'Chrome';
        } else if (preg_match('/Safari/i', $userAgent)) {
            $browser = 'Safari';
        } else if (preg_match('/Opera|OPR/i', $userAgent)) {
            $browser = 'Opera';
        } else if (preg_match('/Edge/i', $userAgent)) {
            $browser = 'Edge';
        } else if (preg_match('/(postman|insomnia|curl)/i', $userAgent)) {
            $browser = 'API Client';
        }

        return [
            'device_type' => $deviceType,
            'os' => $os,
            'browser' => $browser,
            'user_agent' => $userAgent
        ];
    }

    public function revokeDevice(Request $request, $deviceId)
    {
        try {
            $user = $request->user();

            // Check if it's a current device
            $currentTokenId = optional($request->user()->currentAccessToken())->id;
            if ($deviceId == $currentTokenId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cannot revoke current device'
                ], 400);
            }

            // Try to find and revoke token
            $token = $user->tokens()->find($deviceId);
            if ($token) {
                $token->delete();
                return response()->json([
                    'status' => true,
                    'message' => 'Device logged out successfully'
                ]);
            }

            // Try to find and delete session
            $session = DB::table('sessions')
                ->where('id', $deviceId)
                ->where('user_id', $user->id)
                ->first();

            if ($session) {
                DB::table('sessions')
                    ->where('id', $deviceId)
                    ->where('user_id', $user->id)
                    ->delete();

                return response()->json([
                    'status' => true,
                    'message' => 'Device logged out successfully'
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'Device not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to revoke device',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }




    public function exportData(Request $request)
    {
        try {
            $user = $request->user()->load(['addresses', 'orders', 'reviews']);
            
            return response()->json([
                'status' => true,
                'message' => 'Data exported successfully',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to export data',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function deleteAccount(Request $request)
    {
        try {
            $user = $request->user();
            
            // Cannot delete if there are pending orders
            $hasPendingOrders = DB::table('orders')
                ->where('user_id', $user->id)
                ->whereIn('order_status', ['pending_payment', 'confirmed', 'processing', 'shipped'])
                ->exists();
                
            if ($hasPendingOrders) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cannot delete account with active or pending orders'
                ], 400);
            }

            // Revoke all tokens
            $user->tokens()->delete();
            
            // The model uses SoftDeletes so this is a soft delete
            $user->delete();

            return response()->json([
                'status' => true,
                'message' => 'Account deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete account',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}