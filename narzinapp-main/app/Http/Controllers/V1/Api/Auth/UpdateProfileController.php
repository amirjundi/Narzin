<?php

namespace App\Http\Controllers\V1\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UpdateProfileController extends Controller
{
    public function __invoke(Request $request){
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'name' => ['sometimes', 'string', 'max:255'],
                'email' => [
                    'sometimes',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique('users')->ignore($user->id)
                ],
                'current_password' => ['required_with:password'],
                'password' => ['sometimes', 'string', 'min:8', 'confirmed'],
                'user_type_id' => ['prohibited'], 
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check current password if trying to change password
            if ($request->has('password')) {
                if (!Hash::check($request->current_password, $user->password)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Current password is incorrect'
                    ], 422);
                }
            }

            // Update user data
            if ($request->has('name')) {
                $user->name = $request->name;
            }

            $emailChanged = false;
            if ($request->has('email') && $request->email !== $user->email) {
                $user->email = $request->email;
                $user->email_verified_at = null; // Require re-verification
                $emailChanged = true;
            }

            if ($request->has('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            // Send verification email if email was changed
            if ($emailChanged) {
                $user->sendEmailVerificationNotification();
            }

            return response()->json([
                'status' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'email_verified_at' => $user->email_verified_at,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update profile',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    
    }
}
