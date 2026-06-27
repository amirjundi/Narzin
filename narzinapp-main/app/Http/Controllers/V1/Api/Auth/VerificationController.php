<?php

namespace App\Http\Controllers\V1\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\CustomVerifyEmail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Exception\TransportException;

class VerificationController extends Controller
{
    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals(sha1($user->email), $hash)) {
            return view('auth.verification-failed', [
                'message' => 'Invalid verification link'
            ]);
        }

        if ($user->hasVerifiedEmail()) {
            return view('auth.verification-success', [
                'message' => 'Email already verified',
                'webLoginUrl' => config('app.url') . '/login',
                'mobileDeepLink' => config('app.mobile_app_scheme', 'yourapp://') . 'login'
            ]);
        }

        try {
            DB::transaction(function () use ($user) {
                $user->forceFill([
                    'email_verified_at' => Carbon::now()
                ])->save();
            });

            return view('auth.verification-success', [
                'message' => 'Email verified successfully',
                'webLoginUrl' => config('app.url') . '/login',
                'mobileDeepLink' => config('app.mobile_app_scheme', 'yourapp://') . 'login'
            ]);

        } catch (\Exception $e) {
            Log::error('Verification failed: ' . $e->getMessage(), [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);

            return view('auth.verification-failed', [
                'message' => 'Verification failed. Please try again.'
            ]);
        }
    }

    public function resend(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email already verified'
                ], 400);
            }

            // Log email settings for debugging
            Log::info('Attempting to send verification email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'mail_driver' => config('mail.default'),
                'mail_host' => config('mail.mailers.smtp.host')
            ]);

            try {
                $user->notify(new CustomVerifyEmail());

                Log::info('Verification email queued successfully', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Verification link sent successfully',
                    'data' => [
                        'user_id' => $user->id,
                        'email' => $user->email
                    ]
                ]);

            } catch (TransportException $e) {
                Log::error('Mail transport error:', [
                    'message' => $e->getMessage(),
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'Failed to send verification email - Mail server error',
                    'error' => config('app.debug') ? $e->getMessage() : null
                ], 500);

            } catch (\Exception $e) {
                Log::error('Email sending failed:', [
                    'message' => $e->getMessage(),
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'Failed to send verification email',
                    'error' => config('app.debug') ? $e->getMessage() : null
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Resend verification failed:', [
                'message' => $e->getMessage(),
                'id' => $id
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to process verification email request',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    protected function generateVerificationUrl($user)
    {
        
        return route('verification.verify', [
            'id' => $user->id,
            'hash' => sha1($user->email)
        ]);
    }
}