<?php

namespace App\Http\Controllers\V1\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Vendor\Models\Vendor;

class LoginController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => ['required', 'string', 'email'],
                'password' => ['required', 'string'],
            ]);

            $user = User::where('email', $validated['email'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            if (!$user->hasVerifiedEmail()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email not verified',
                    'data' => [
                        'verification_required' => true,
                        'user_id' => $user->id,
                        'resend_verification_url' => route('verification.send', ['id' => $user->id])
                    ]
                ], 403);
            }

            // For stateful SPA (cookie) clients, establish a session so the
            // browser authenticates via the httpOnly session cookie instead of
            // a token held in JS. Token clients (mobile) have no session here,
            // so this is a no-op for them and they continue to use the Bearer
            // token returned below.
            if ($request->hasSession()) {
                Auth::login($user);
                $request->session()->regenerate();
            }

            if ($request->has('vendor')) {
                $isVendor = Vendor::where('user_id', $user->id)->exists();

                if (!$isVendor) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Authentication failed',
                        'errors' => [
                            'email' => ['Users accounts are not allowed to login here.']
                        ]
                    ], 403);
                }
            
                $vendorData = Vendor::where('user_id', $user->id)->first();
                $user->tokens()->where('name', 'LIKE', '%' . $request->userAgent() . '%')->delete();

                // Create token with device info
                $tokenName = json_encode([
                    'user_agent' => $request->userAgent(),
                    'ip' => $request->ip(),
                    'created_at' => now()->toIso8601String(),
                    'device_type' => $this->detectDeviceType($request->userAgent())
                ]);
    
                $token = $user->createToken($tokenName)->plainTextToken;
    
                return response()->json([
                    'status' => true,
                    'message' => 'Login successful',
                    'data' => [
                        'user' => $user->only(['id', 'name', 'email', 'created_at', 'email_verified_at']),
                        'vendor_details' => $vendorData,
                        'token' => $token,
                        'token_type' => 'Bearer'
                    ]
                ]);
            
            }

            // Delete existing tokens with the same user agent
            $user->tokens()->where('name', 'LIKE', '%' . $request->userAgent() . '%')->delete();

            // Create token with device info
            $tokenName = json_encode([
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip(),
                'created_at' => now()->toIso8601String(),
                'device_type' => $this->detectDeviceType($request->userAgent())
            ]);

            $token = $user->createToken($tokenName)->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => $user->only(['id', 'name', 'email', 'created_at', 'email_verified_at']),
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Authentication failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    private function detectDeviceType($userAgent)
    {
        $deviceType = 'Desktop';

        if (preg_match('/(PostmanRuntime)/i', $userAgent)) {
            $deviceType = 'API Client';
        } elseif (preg_match('/(iPhone|iPod|iPad|Android|BlackBerry|webOS)/i', $userAgent)) {
            $deviceType = 'Mobile';
        } elseif (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobile))/i', $userAgent)) {
            $deviceType = 'Tablet';
        }

        return $deviceType;
    }
}
