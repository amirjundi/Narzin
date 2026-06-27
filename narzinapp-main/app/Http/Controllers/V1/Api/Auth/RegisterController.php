<?php

namespace App\Http\Controllers\V1\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\Registered;

class RegisterController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'confirmed', Password::defaults()],
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'user_type_id' => 1,
            ]);

            // Send verification email
            $user->notify(new CustomVerifyEmail());

            // Create token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'User registered successfully. Please check your email for verification link.',
                'data' => [
                    'user' => $user->only(['id', 'name', 'email', 'created_at']),
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'verification_status' => [
                        'verified' => false,
                        'user_id' => $user->id,
                        'message' => 'Please verify your email address'
                    ]
                ]
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Registration failed',
                'errors' => ['general' => [$e->getMessage()]],
            ], 500);
        }
    }
}