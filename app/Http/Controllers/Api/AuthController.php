<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {

            $validated = Validator::make($request->all(), [
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            if ($validated->fails()) {
                return ApiResponse::error('Validation Error', $validated->errors(), 401);
            }

            $user = User::where('email', $request->email)->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                return ApiResponse::error('Invalid credentials', null, 401);
            }

            $token = $user->createToken('flutter-app')->plainTextToken;

            return ApiResponse::success([
                'user' => $user,
                'permissions' => $user->getPermissionsViaRoles(),
                'token' => $token,
            ], 'Login successful');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), null, 500);
        }
    }

    public function me(Request $request)
    {
        return ApiResponse::success([
            'user' => $request->user(),
        ], 'User information retrieved successfully');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success(null, 'Logged out successfully');
    }

    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();

        return ApiResponse::success(null, 'Logged out from all devices successfully');
    }

    /**
     * Handle forgot password request
     */
    public function forgotPassword(Request $request)
    {
        try {
            // Validate the request
            $validated = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
            ], [
                'email.required' => 'The email field is required.',
                'email.email' => 'Please enter a valid email address.',
                'email.exists' => 'We could not find a user with that email address.',
            ]);

            if ($validated->fails()) {
                return ApiResponse::error('Validation Error', $validated->errors(), 422);
            }

            // Generate a password reset token
            $token = Str::random(64);

            // Store the token in the password_resets table
            DB::table('password_resets')->updateOrInsert(
                ['email' => $request->email],
                [
                    'email' => $request->email,
                    'token' => Hash::make($token),
                    'created_at' => Carbon::now(),
                ]
            );

            // Send the password reset email
            $user = User::where('email', $request->email)->first();
            $resetUrl = url("/api/v1/reset-password?token={$token}&email={$request->email}");
            $companyName = Setting::get('company_name', 'Technofra Admin');

            try {
                Mail::send('emails.password-reset', [
                    'user' => $user,
                    'resetUrl' => $resetUrl,
                    'token' => $token,
                    'companyName' => $companyName,
                ], function ($message) use ($user, $companyName) {
                    $message->to($user->email, $user->name)
                        ->subject('Reset Your Password - '.$companyName);
                });

                return ApiResponse::success(null, 'We have sent a password reset link to your email address.');
            } catch (\Exception $e) {
                // Log the error for debugging
                Log::error('Password reset email failed: '.$e->getMessage());

                return ApiResponse::error('Failed to send password reset email. Please try again.', null, 500);
            }
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), null, 500);
        }
    }

    /**
     * Handle password reset
     */
    public function resetPassword(Request $request)
    {
        try {
            // Validate the request
            $validated = Validator::make($request->all(), [
                'token' => 'required',
                'email' => 'required|email|exists:users,email',
                'password' => 'required|string|min:6|confirmed',
            ], [
                'email.required' => 'The email field is required.',
                'email.email' => 'Please enter a valid email address.',
                'email.exists' => 'We could not find a user with that email address.',
                'password.required' => 'The password field is required.',
                'password.min' => 'Password must be at least 6 characters.',
                'password.confirmed' => 'Password confirmation does not match.',
            ]);

            if ($validated->fails()) {
                return ApiResponse::error('Validation Error', $validated->errors(), 422);
            }

            // Verify the token
            $passwordReset = DB::table('password_resets')
                ->where('email', $request->email)
                ->first();

            if (! $passwordReset || ! Hash::check($request->token, $passwordReset->token)) {
                return ApiResponse::error('Invalid or expired password reset token.', null, 422);
            }

            // Check if token is expired (24 hours)
            if (Carbon::parse($passwordReset->created_at)->addHours(24)->isPast()) {
                DB::table('password_resets')->where('email', $request->email)->delete();

                return ApiResponse::error('Password reset token has expired. Please request a new one.', null, 422);
            }

            // Update the user's password
            $user = User::where('email', $request->email)->first();
            $user->password = Hash::make($request->password);
            $user->save();

            // Delete the password reset token
            DB::table('password_resets')->where('email', $request->email)->delete();

            return ApiResponse::success(null, 'Your password has been reset successfully. You can now login with your new password.');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), null, 500);
        }
    }
}
